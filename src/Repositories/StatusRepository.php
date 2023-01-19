<?php
/**
 * Created by PhpStorm.
 * User: iankibet
 * Date: 5/12/18
 * Time: 10:16 AM
 */

namespace Cih\Framework\Repositories;

class StatusRepository
{
    public static function getMessageStatus($state)
    {
        $statuses = [
            'Success' => 101,
            'Queued' => 102,
            'RiskHold' => 401,
            'InvalidSenderId' => 402,
            'InvalidPhoneNumber' => 403,
            'Processed' => 100,
            'UnsupportedNumberType' => 404,
            'InsufficientBalance' => 405,
            'UserInBlackList' => 406,
            'CouldNotRoute' => 407,
            'InternalServerError' => 500,
            'GatewayError' => 501,
            'RejectedByGateway' => 502,

        ];
        if (is_numeric($state))
            $statuses = array_flip($statuses);
        if (is_array($state)) {
            $states = [];
            foreach ($state as $st) {
                $states[] = $statuses[$st];
            }
            return $states;
        }
        return $statuses[$state];
    }
    public static function getMessageFailedReason($state)
    {
        $statuses = [
            'N/A' => 0,
            'InsufficientCredit' => 1,
            'InvalidLinkId' => 2,
            'UserIsInactive' => 3,
            'UserInBlackList' => 4,
            'UserAccountSuspended' => 5,
            'NotNetworkSubcriber' => 6,
            'UserNotSubscribedToProduct' => 7,
            'UserDoesNotExist' => 8,
            'DeliveryFailure' => 9

        ];
        if (is_numeric($state))
            $statuses = array_flip($statuses);
        if (is_array($state)) {
            $states = [];
            foreach ($state as $st) {
                $states[] = $statuses[$st];
            }
            return $states;
        }
        return $statuses[$state];
    }

    public static function getMessageDeliveryStatus($state)
    {

        $statuses = [
            'Sent' => 0,
            'Message Sent' => 1,
            'Submitted' => 2,
            'Buffered' => 3,
            'Rejected' => 4,
            'Success' => 5,
            'Failed' => 6

        ];
        if (is_numeric($state))
            $statuses = array_flip($statuses);
        if (is_array($state)) {
            $states = [];
            foreach ($state as $st) {
                $states[] = $statuses[$st];
            }
            return $states;
        }
        return $statuses[$state];
    }

    public static function getTicketStatus($state)
    {
        $statuses = [
            // '' => 0,
            'open' => 1,
            'Open' => 1,
            'resolved' => 2,
            'Resolved' => 2,
            'closed' => 3,
            'Closed' => 3,
            'In Progress' => 4
        ];
        return self::checkState($state, $statuses);
    }

    public static function getActiveStatus($state)
    {
        $statuses = [
            'Inactive' => 0,
            'Active' => 1,
        ];

        if($state === 'all'){
            return $statuses;
        }

        return self::checkState($state, $statuses);
    }

    public static function getSwitchBoardState($state)
    {
        $statuses = [
            'No' => 0,
            'Yes' => 1,
        ];

        if($state === 'all'){
            return $statuses;
        }

        return self::checkState($state, $statuses);
    }

    public static function getTicketPriority($state)
    {
        $statuses = [
            'Low' => 1,
            'Normal' => 2,
            'High' => 3,
            'Urgent' => 4,

        ];
        return self::checkState($state, $statuses);
    }

    public static function getInvoicePaymentStatus($state)
    {
        $statuses = [
            'unpaid' => 0,
            'paid' => 1,
            'partially_paid' => 2,
            'overdue' => 3,
        ];
        return self::checkState($state, $statuses);
    }
    public static function getUserStatus($state)
    {
        $statuses = [
            'Inactive' => 0,
            'Active' => 1,
        ];
        return self::checkState($state, $statuses);
    }

    public static function getInvoiceStatus($state)
    {
        $statuses = [
            'draft' => 0,
            'send' => 1
        ];
        return self::checkState($state, $statuses);
    }
    public static function getQuotationStatus($state){
        $statuses = [
            'pending'=>0,
            'send'=>1,
            'invoice_generated'=>2,
        ];
        return self::checkState($state, $statuses);
    }

    public static function getOpportunitiesStatus($state){
        $statuses = [
            'Creation'=>0,
            'To Contact'=>1,
            'contact'=>1,
            'Contact'=>1,
            'Collecting specs'=>2,
            'Collecting Specifications'=>2,
            'Collecting Specs'=>2,
            'with_estimator'=>3,
            'with estimator'=>3,
            'With Estimator'=>3,
            'quote sent'=>4,
            'quote_sent'=>4,
            'Quote Sent'=>4,
            'In negotiation'=>5,
            'in_negotiation'=>5,
            'In Negotiation'=>5,
            'Lpo received'=>6,
            'lpo_received'=>6,
            'LPO Received'=>6,
            'Closed lost'=>7,
            'closed_lost'=>7,
            'Closed Lost'=>7,
            'Closed won'=>8,
            'closed_won'=>8,
            'Closed Won'=>8,
        ];
        return self::checkState($state, $statuses);
    }

    public static function getPermissionGroup($state){
        $statuses = [
            'Admin'=>1,
            'Sales Manager'=>2,
            'Sales Agents'=>3,
            'Company CEO'=>4,
            'Group CEO'=>5,
            'Board Member'=>6,
            'Vertical CEO'=>7,
        ];
        return self::checkState($state, $statuses);
    }
    public static function getSaleRevertStatus($state){
        $statuses = [
            'not_reverted'=>0,
            'reverted'=>1
        ];
        return self::checkState($state, $statuses);
    }

    public static function checkState($state, $statuses)
    {
        if (is_numeric($state))
            $statuses = array_flip($statuses);
        if (is_array($state)) {
            $states = [];
            foreach ($state as $st) {
                $states[] = $statuses[$st];
            }

            return $states;
        }
        return $statuses[$state];
    }
    public static function getSlaStatus($state)
    {
//        time left, violated, fulfilled
        $statuses = [
            'Inactive' => 0,
            'Time Left' => 1,
            'Violated' => 2,
            'Fulfilled' => 3,
        ];
        if (is_numeric($state))
            $statuses = array_flip($statuses);
        if (is_array($state)) {
            $states = [];
            foreach ($state as $st) {
                $states[] = $statuses[$st];
            }
            return $states;
        }
        return $statuses[$state];
    }

    public static function getDaysOfTheWeek($state)
    {
        $statuses = [
            'Sunday' => 7,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,

        ];
        if (is_numeric($state))
            $statuses = array_flip($statuses);
        if (is_array($state)) {
            $states = [];
            foreach ($state as $st) {
                $states[] = $statuses[$st];
            }
            return $states;
        }
        return $statuses[$state];
    }

    public static function getAppointmentStatus($state)
    {
        $statuses = [
            'Cancelled' => 0,
            'Booked' => 1,
            'Rescheduled' => 2,
            'Closed' => 3

        ];
        if (is_numeric($state))
            $statuses = array_flip($statuses);
        if (is_array($state)) {
            $states = [];
            foreach ($state as $st) {
                $states[] = $statuses[$st];
            }
            return $states;
        }
        return $statuses[$state];
    }


}
