<?php

namespace Cih\Framework\Middleware;

use Cih\Framework\Repositories\RoleRepository;
use Closure;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Jenssegers\Agent\Agent;

class AuthMenus
{
    protected $router;
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if(!$request->isXmlHttpRequest()){
            if($request->t_optimized || $request->ta_optimized){
//                return redirect($request)
                $data = $request->all();
                if(isset($data['t_optimized']))
                    unset($data['t_optimized']);
                if(isset($data['ta_optimized']))
                    unset($data['ta_optimized']);
                return redirect($request->path().'?'.http_build_query($data));
            }
        }
        if(!$request->session()->get('agent_set')){
            $agent = new Agent();
            $request->session()->put('is_mobile',$agent->isMobile());
            $request->session()->put('agent_set',1);
        }
        $request->session()->put('agent_set',0);
        View::share('is_mobile',$request->session()->get('is_mobile'));
        $action_name = $this->router->getRoutes()->match($request)->getActionName();
        $end = explode('@',$action_name)[0];
        $controller = @explode('Controllers',$end)[1];
        $controller = stripslashes($controller);
        View::share('controller',$controller);
        $tab = $request->tab;
        $tab = str_replace('-','_',$tab);
        View::share('tab',$tab);
        $role_repo = new RoleRepository($request);
        $role_repo->check();
        $user = $request->user();
        if($user){
            if($request->session()->get('needs_otp')){
                if($controller != 'UserOtpController')
                    return redirect("user/otp");
            }
        }
        $response = $next($request);
        if($request->isXmlHttpRequest() && $response->getStatusCode() == 302){
            $response->headers->set('content-type','application/json');
            $target_url = $response->getTargetUrl();
            $new_content = ['redirect'=>$target_url];
            $responsecode = 200;
            $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );
            return response()->json($new_content , $responsecode, $header, JSON_UNESCAPED_UNICODE);
        }
        return $response;
    }

    protected function formatPhone($phone){
        $len = strlen($phone);
        if($len==10){
            $phone = "repl".$phone;
            $phone = str_replace('repl07','+2547',$phone);
        }
        if($len==12){
            $phone = '+'.$phone;
        }
        return $phone;
    }

    public function checkApprovedAgent($user) {

    }
}
