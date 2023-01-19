<?php
/**
 * Created by PhpStorm.
 * User: iankibet
 * Date: 2016/06/04
 * Time: 7:47 AM
 */

namespace Cih\Framework\Repositories;


use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

class RoleRepository
{
    protected $path;
    protected $user;
    protected $menus;
    protected $allow = false;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = Auth::user();
        $this->path = Route::getFacadeRoot()->current()->uri();
        $sub_path = strtolower($this->path);
        $sub_path = str_replace('unpaid', 'bids', $sub_path);
        $sub_path = str_replace('disputes', 'resolution', $sub_path);
        $sub_path = str_replace('stud', 'Home', $sub_path);
        $sub_pages = explode('/', $sub_path);
        View::share('sub_pages', $sub_pages);

    }

    public function check($allow = false)
    {
        $path_sections = explode('/', $this->path);
        if (in_array('api', $path_sections)) {
            return true;
        }
        $this->allow = $allow;
        $file = Storage::disk('local')->get('system/roles.json');
        $menus = json_decode($file);

        $this->menus = $menus;

        if (!$menus) {
            die('Error decoding json config');
        }
        if (Auth::user()) {
            $user = Auth::user();
            $role = $this->user->role;
            if (!$role) {
                $this->user->role = "admin22";
                $this->user->update();
                $role = "admin";
            }
            $allowed = $menus->$role;

            if ($this->user->role == 'admin') {
                $admins = User::where('role', 'admin')->count();
                //assumed the first admin account is the developers
                if ($admins > 0) {
                    $permissions = [];
                    if ($this->user->permissionGroup)
                        $permissions = json_decode($this->user->permissionGroup->permissions); //allowed permissions
                    $allowed = $this->getFormatedAllowedMenus($menus->$role, $permissions);

                    $this->authorize($allowed, $menus->guest);
                } else {
                    //This gives default Admin {First Admin} all priviledges
                    $allowed = [];
                    foreach ($menus->admin as $mnu) {
                        $allowed[] = $mnu;
                    }
                    $this->authorize($allowed, $menus->guest);
                }
            } else {
//                foreach($this->menus->in as $mnu){
//                    $allowed[]=$mnu;
//                }

                $this->authorize($allowed, $menus->guest);
//                View::share('real_menus',$allowed);
            }
        } else {
            $out_menus = $menus->guest;
            foreach ($this->menus->out as $mnu) {
                $out_menus[] = $mnu;
            }
            View::share('real_menus', $out_menus);
        }

    }

    protected function authorize($backend, $front_end)
    {
        $current = preg_replace('/\d/', '', $this->path);
        $current = preg_replace('/{(.*?)}/', '', $current);
        $current = rtrim($current, '/');
        View::share('current_url', $current);
//        if($this->user->role=='business'){
//            $business = $this->filterBackend($business);
//        }

        $backend_urls = $this->separateAllLinks($backend);
        $front_end_urls = $this->separateAllLinks($front_end);

//dd($backend_urls);
        $current = str_replace("//", "/", $current);

        if (in_array($current, $backend_urls)) {
            View::share(['real_menus' => $backend]);
        } elseif (in_array($current, $front_end_urls) || $this->allow == true) {
            foreach ($this->menus->in as $mnu) {
                $front_end[] = $mnu;
            }
            View::share('real_menus', $front_end);
        } else {
            $this->unauthorized();
        }

    }

    public function filterBackend($backend)
    {
        $allowed = [];
        if ($this->user->role == 'business') {
            $group_permissions = $this->user->userGroup->permissions;

        } elseif ($this->user->role == 'super') {
            $group_permissions = json_decode($this->user->group->permissions);
        }
        if (!$group_permissions) {
            $group_permissions = [];
        }
        foreach ($backend as $single) {
            if (in_array($single->slug, $group_permissions)) {
                $allowed[] = $single;
                if ($single->slug == 'user_management') {
                    $user_groups = UserGroup::all(['id', 'name']);
                    foreach ($user_groups as $group) {
                        $menu = new \stdClass();
                        $menu->url = "users/view/" . $group->id;
                        $menu->label = $group->name;
                        $single->children[] = $menu;
                    }
                }
            }

        }
        return $allowed;
    }

    protected function separateLinks($raw_menu)
    {
        $links = [];
        foreach ($raw_menu as $single) {

            $main_url = "";
            if ($single->type == 'many') {
                foreach ($single->children as $key => $child) {
//                    if($key > 2)
//                        dd($child,$raw_menu);
                    $child_url = preg_replace('/\d/', '', $child->url);
                    $child_url = rtrim($child_url, '/');
                    if (!in_array($child_url, $links))
                        $links[] = $child_url;
                }
                if (isset($single->urls)) {
                    foreach ($single->urls as $url) {
                        $url = rtrim($url, '/');
                        $url = preg_replace('/\d/', '', $url);
                        if (!in_array($url, $links))
                            $links[] = $url;
                    }
                }

                if (isset($single->subs) && isset($single->main)) {
                    $child_url = preg_replace('/\d/', '', $single->main);
                    $child_url = rtrim($child_url, '/');
                    $main_url = $child_url;
                    foreach ($single->subs as $url) {
                        $url = rtrim($url, '/');
                        $url = preg_replace('/\d/', '', $url);
                        $url = $main_url . '/' . $url;
                        if (!in_array($url, $links))
                            $links[] = $url;
                    }
                }
            } else {
                if (isset($single->menus->url)) {
                    $child_url = preg_replace('/\d/', '', $single->menus->url);
                    $child_url = rtrim($child_url, '/');
                    $main_url = $child_url;
                    if (!in_array($child_url, $links))
                        $links[] = $child_url;
                }
                if (isset($single->subs)) {
                    foreach ($single->subs as $url) {
                        $url = rtrim($url, '/');
                        $url = preg_replace('/\d/', '', $url);
                        $url = $main_url . '/' . $url;
                        if (!in_array($url, $links))
                            $links[] = $url;
                    }
                }
            }
            if (isset($single->urls))
                foreach ($single->urls as $url) {
                    $url = rtrim($url, '/');
                    $url = preg_replace('/\d/', '', $url);
                    if (!in_array($url, $links))
                        $links[] = $url;
                }
        }
        return $links;
    }

    protected function separateAllLinks($raw_menu)
    {
        $links = [];
        foreach ($raw_menu as $single) {
            $main_url = "";
            if ($single->type == 'many') { //fetch 2nd level child menu urls ie (urls and subs)
                foreach ($single->children as $child) {
                    $child_url = $this->formatUrl($child->url);
                    if (!in_array($child_url, $links)) // push url to allowed array if doesn't exist
                        $links[] = $child_url;
                    // get child menu urls (subs - concat with the main url plus other full urls included in section)
                    $links = $this->getAllMenuUrls($child->url, $links, @$child->subs, @$child->urls);

                    //get grand children links
                    if ($child->type == "many" && isset($child->children))
                        foreach ($child->children as $grand_child) {
                            $grand_child_url = $this->formatUrl($grand_child->url);
                            if (!in_array($grand_child_url, $links)) // push url to allowed array if doesn't exist
                                $links[] = $grand_child_url;
                        }
                }
                if (isset($single->urls)) { //fetch main urls 1st level ie subs and urls
                    $links = $this->getAllMenuUrls($single->main, $links, $single->subs, $single->urls);
                }

            } else { //fetch subs and urls for single  1st level url menus
                if (isset($single->menus->url)) {
                    $main_url = $this->formatUrl($single->menus->url);
                    if (!in_array($main_url, $links))
                        $links[] = $main_url;
                    $links = $this->getAllMenuUrls($main_url, $links, $single->subs, $single->urls);
                }
            }

        }
        return $links;
    }

    protected function getAllMenuUrls($main_url, $links, $subs = [], $full_urls = [])
    {
        $main_url = $this->formatUrl($main_url);
        if (isset($subs))
            foreach ($subs as $url) { //concat sub-urls with main url and push to allowed urls if doesn't exist
                $url = $this->formatUrl($url); // format url ie remove end slash and whitespaces
                $url = ($url) ? $main_url . '/' . $url : $main_url;
                if (!in_array($url, $links))
                    $links[] = $url;
            }
        if (isset($full_urls))
            foreach ($full_urls as $url) { // push the section urls into the allowed urls array
                $url = $this->formatUrl($url);
                if (!in_array($url, $links))
                    $links[] = $url;
            }
        return $links;
    }

    public function formatUrl($url)
    {
        $url = rtrim($url, '/');
        $url = preg_replace('/\d/', '', $url);
        return $url;
    }

    public function getFormatedAllowedMenus($menus, $permissions)
    {
        $allowed = [];
        foreach ($menus as $mnu) { //
            if (in_array($mnu->slug, $permissions)) {
                if ($mnu->type == 'many') {
                    $allowed_children = [];
                    foreach ($mnu->children as $key => $child) {
                        if (in_array($child->slug, $permissions))
                            $allowed_children[] = $child;
                    }
                    $mnu->children = $allowed_children;
                }
                $allowed[] = $mnu;
            }
        }
        return $allowed;
    }

    public function unauthorized()
    {
        $common_paths = ['logout', 'login', 'register'];
        $path = $this->path;
//        dd($path,$common_paths);
        if (!in_array($path, $common_paths)) {
            App::abort(403);
            die('You are not authorized to perform this action');
        }
    }
}
