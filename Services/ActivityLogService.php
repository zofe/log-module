<?php

namespace App\Modules\Log\Services;

use App\Models\User;
use App\Modules\Log\Models\Activity;
use App\Modules\Weathermaps\Models\WeathermapObject;
use Cron\CronExpression;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Lorisleiva\CronTranslator\CronTranslator;

class ActivityLogService
{
    function prependKeysWithColon(array $array): array {
        $arrayWithNewKeys = [];
        foreach ($array as $key => $value) {
            $newKey = ':' . $key;
            $arrayWithNewKeys[$newKey] = $value;
        }
        return $arrayWithNewKeys;
    }

    function translateCron(string $cronExpression): string {
        $cronVals = ['mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7];
        $parts = CronExpression::factory($cronExpression)->getParts();
        if(isset($parts[4]) && $parts[4] != "*"){
            $newPartValue = "";
            foreach (explode(",", $parts[4]) as $key => $cronPartVal){
                $endChar = ($key === array_key_last(explode(",", $parts[4]))) ? "" : ",";
                $newPartValue .= "{$cronVals[$cronPartVal]}{$endChar}";
            }
            $parts[4] = $newPartValue;
        }
        try{
            return CronTranslator::translate(implode(" ", $parts));
        }catch (\Exception $e){
            Log::error($e);
            return "";
        }
    }

    function defineSourceDestinationZtnaRule($ztna, array $array): array{
        if(!isset($array[":source_model_type"])){
            $sourceType = $ztna->source_model_type;
        }else{
            $sourceType = $array[":source_model_type"];
        }

        if(!isset($array[":destination_model_type"])){
            $destinationType = $ztna->destination_model_type;
        }else{
            $destinationType = $array[":destination_model_type"];
        }

        if(isset($array[":source_model_id"])){
            $sourceFrom = app($sourceType)->find($array[":source_model_id"]);
            $array[":source"] = $sourceFrom->name;
        }

        if(isset($array[":destination_model_id"])){
            $destinationFrom = app($destinationType)->find($array[":destination_model_id"]);
            $array[":destination"] = $destinationFrom->name;
        }

        if(isset($array[":direction"])){
            $array[":direction"] = __('log::log.value_direction_'.$array[":direction"]);
        }

        Arr::forget($array, [
            ":source_model_type",
            ":source_model_id",
            ":destination_model_type",
            ":destination_model_id",
        ]);
        return $array;
    }

    public static function makeActivityLogReadable(Activity $log)
    {
        $message = __("log::log.{$log->log_name}");
        $otherKeys = Arr::except($log->properties->toArray(), ['settings', 'added', 'removed', 'from', 'to']);
        $message = strtr($message, $otherKeys);
        if(isset($log->properties->toArray()['added']) || isset($log->properties->toArray()['removed'])){
            $hash = md5("activity_log_ephest_0c7c7501-4402-4f6c-ac02-7079b2dd631d_{$log->id}");
            $message = "<div>{$message} <i class='fas fa-chevron-right text-primary' data-bs-toggle='collapse' href='#al_{$hash}' role='button' aria-expanded='false' aria-controls='al_$hash'></i></div><div class='collapse' id='al_$hash' style='font-size: 12px;'>";
            foreach($log->properties->toArray()['removed'] as $c2 => $removedRole){
                $endChar = ($c2 === array_key_last($log->properties->toArray()['removed'])) ? "<br>" : ", ";
                $message.="<del>{$removedRole}</del>{$endChar}";
            }
            foreach($log->properties->toArray()['added'] as $c => $addedRole){
                $endChar = ($c === array_key_last($log->properties->toArray()['added'])) ? "" : ", ";
                $message.="<ins>{$addedRole}</ins>{$endChar}";
            }
            $message.="</div>";
            return $message;
        }elseif(isset($log->properties->toArray()['settings'])){
            $hash = md5("activity_log_ephest_0c7c7501-4402-4f6c-ac02-7079b2dd631d_{$log->id}");
            $message = "<div>{$message} <i class='fas fa-chevron-right text-primary' data-bs-toggle='collapse' href='#al_{$hash}' role='button' aria-expanded='false' aria-controls='al_$hash'></i></div><div class='collapse' id='al_$hash' style='font-size: 12px;'>";
            foreach($log->properties->toArray()['settings'] as $setting => $value){
                $message.="<b>".__("log::log.{$setting}")."</b> => <ins>$value</ins><br>";
            }
            $message.="</div>";
            return $message;
        }elseif(!isset($log->properties->toArray()['from']) || !isset($log->properties->toArray()['to'])){
            //Non deve essere formattato nel modo tradizionale
            return strtr(__("log::log.{$log->log_name}"), $log->properties->toArray());
        }
        $changedKeys = array_keys($log->properties->toArray()['from']);
        $hash = md5("activity_log_ephest_0c7c7501-4402-4f6c-ac02-7079b2dd631d_{$log->id}");
        $message = "<div>{$message} <i class='fas fa-chevron-right text-primary' data-bs-toggle='collapse' href='#al_{$hash}' role='button' aria-expanded='false' aria-controls='al_$hash'></i></div><div class='collapse' id='al_$hash' style='font-size: 12px;'>";
        foreach ($changedKeys as $c => $changedKey){
            $readableKey = __("log::log.key_".str_replace(":", "", $changedKey));
            $endChar = ($c === array_key_last($changedKeys)) ? "" : "<br>";

            if(is_array($log->properties->toArray()['from'][$changedKey]) || is_array($log->properties->toArray()['to'][$changedKey])){
                $message.= "<div><b>{$readableKey}: </b>";
                foreach((array)$log->properties->toArray()['from'][$changedKey] as $c2 => $fromVal){
                    $endCharFrom = ($c2 === array_key_last($log->properties->toArray()['from'][$changedKey])) ? "<br>" : ", ";
                    $message.="<del>{$fromVal}</del>{$endCharFrom}";
                }
                foreach((array)$log->properties->toArray()['to'][$changedKey] as $c3 => $toVal){
                    $endCharTo = ($c3 === array_key_last($log->properties->toArray()['to'][$changedKey])) ? "" : ", ";
                    $message.="<ins>{$toVal}</ins>{$endCharTo}";
                }
            }else{
                $fromValue = ($log->properties->toArray()['from'][$changedKey]) ? "<del>{$log->properties->toArray()['from'][$changedKey]}</del>" : "<span style='opacity: 0.6;'>empty</span>";
                $toValue = ($log->properties->toArray()['to'][$changedKey]) ? "<ins>{$log->properties->toArray()['to'][$changedKey]}</ins>" : "<span style='opacity: 0.6;'>empty</span>";
                $message.= "<div><b>{$readableKey}:</b> {$fromValue} => {$toValue}{$endChar}</div>";
            }
        }
        $message.="</div>";
        return $message;
    }

    public static function makeActivityLogStructured(Activity $log): array
    {
        $props = $log->properties->toArray();
        $otherKeys = Arr::except($props, ['settings', 'added', 'removed', 'from', 'to']);
        $action = strtr(__("log::log.{$log->log_name}"), $otherKeys);

        $result = [
            'log_name' => $log->log_name,
            'action' => $action,
            'changes' => [],
        ];

        if (isset($props['added']) || isset($props['removed'])) {
            $result['added'] = array_values($props['added'] ?? []);
            $result['removed'] = array_values($props['removed'] ?? []);
            return $result;
        }

        if (isset($props['settings'])) {
            $result['settings'] = $props['settings'];
            return $result;
        }

        if (isset($props['from']) && isset($props['to'])) {
            foreach (array_keys($props['from']) as $key) {
                $result['changes'][] = [
                    'field' => str_replace(':', '', $key),
                    'from' => $props['from'][$key],
                    'to' => $props['to'][$key] ?? null,
                ];
            }
        }

        return $result;
    }

    public static function storeUserActivityLog($logName, $user, $changes, $userFrom, $userTo)
    {
        $smtpPasswordChanged = false;
        $excludedFields = ['smtp_password', 'updated_at'];
        $logNameDetail = $logName;

        //The Smtp password changed
        if(isset($userFrom['smtp_password']) && $userFrom['smtp_password'] && isset($userTo['smtp_password']) && $userTo['smtp_password']){
            if(Crypt::decryptString($userFrom['smtp_password']) != Crypt::decryptString($userTo['smtp_password'])){
                $excludedFields = ['updated_at'];
                $smtpPasswordChanged = true;
            }
        }

        if(Str::contains($logName, 'roles')){
            $addedPermissions = array_values(array_diff($userTo, $userFrom));
            $removedPermissions = array_values(array_diff($userFrom, $userTo));
            if(!empty($addedPermissions) || !empty($removedPermissions)){
                log_activity($logName, $user, ['added' => $addedPermissions, 'removed' => $removedPermissions]);
            }
            return true;
        }

        $changes = Arr::except($changes, $excludedFields);

        $userFieldFrom = Arr::only($userFrom, config("log.visible_properties.{$logName}"));
        $userFieldTo = Arr::only($userTo, config("log.visible_properties.{$logName}"));

        if(Str::contains($logName, 'edit')){
            $userFieldFrom = Arr::only($userFieldFrom, array_keys($changes));
            $userFieldTo = Arr::only($userFieldTo, array_keys($changes));
        }

        foreach (array_keys($userFieldFrom) as $key){
            if (Str::contains($key, 'telegram')) {
                $logNameDetail = "{$logName}@telegram";
            }elseif(Str::contains($key, 'smtp')){
                $logNameDetail = "{$logName}@smtp";
            }
        }

        if($logName != $logNameDetail){
            $logName = $logNameDetail;
        }

        if($smtpPasswordChanged){
            Arr::set($userFieldFrom, 'smtp_password', 'REDACTED');
            Arr::set($userFieldTo, 'smtp_password', 'REDACTED');
        }

        $userFieldFrom = (new ActivityLogService)->prependKeysWithColon($userFieldFrom);
        $userFieldTo = (new ActivityLogService)->prependKeysWithColon($userFieldTo);

        if(empty($userFieldFrom) && Str::contains($logName, 'create')) {
            $userFieldFrom = $userFieldTo;
            foreach($userFieldFrom as $key => $field) {
                $userFieldFrom[$key] = null;
            }
        }

        if (!empty($userFieldFrom) && !empty($userFieldTo)) {
            if (Str::contains($logName, 'edit') && !empty($changes)) {
                log_activity($logName, $user, ['from' => $userFieldFrom, 'to' => $userFieldTo]);
            } elseif (Str::contains($logName, 'create')) {
                log_activity($logName, $user, ['from' => $userFieldFrom, 'to' => $userFieldTo]);
            }
        }

        return true;
    }

    public static function storeCompanyActivityLog($logName, $company, $changes, $companyFrom, $companyTo)
    {
        $companyFieldFrom = Arr::only($companyFrom, config("log.visible_properties.{$logName}"));
        $companyFieldTo = Arr::only($companyTo, config("log.visible_properties.{$logName}"));

        if(Str::contains($logName, 'edit')){
            $companyFieldFrom = Arr::only($companyFieldFrom, array_keys($changes));
            $companyFieldTo = Arr::only($companyFieldTo, array_keys($changes));
        }

        $companyFieldFrom = (new ActivityLogService)->prependKeysWithColon($companyFieldFrom);
        $companyFieldTo = (new ActivityLogService)->prependKeysWithColon($companyFieldTo);

        if(empty($companyFieldFrom) && Str::contains($logName, 'create')) {
            $companyFieldFrom = $companyFieldTo;
            foreach($companyFieldFrom as $key => $field) {
                $companyFieldFrom[$key] = null;
            }
        }

        //If auth()->user() returns null the code execution is not done via a Browser session but rather via API,
        // so I consider the causer_id as the owner of the company.
        $causer_id = null;
        if(!auth()->user()){
            $causer_id = optional(optional($company->parent)->owner)->id;
        }

        if (!empty($companyFieldFrom) && !empty($companyFieldTo)) {
            if (Str::contains($logName, 'edit') && !empty($changes)) {
                log_activity($logName, $company, ['from' => $companyFieldFrom, 'to' => $companyFieldTo], null, $causer_id);
            } elseif (Str::contains($logName, 'create')) {
                log_activity($logName, $company, ['from' => $companyFieldFrom, 'to' => $companyFieldTo], null, $causer_id);
            }
        }
        return true;
    }

    public static function storeCompanyRoleActivityLog($logName, $companyRole, $changes, $companyRoleFrom, $companyRoleTo)
    {
        $companyRoleFieldFrom = Arr::only($companyRoleFrom, config("log.visible_properties.{$logName}"));
        $companyRoleFieldTo = Arr::only($companyRoleTo, config("log.visible_properties.{$logName}"));

        if(Str::contains($logName, 'edit')){
            $companyRoleFieldFrom = Arr::only($companyRoleFieldFrom, array_keys($changes));
            $companyRoleFieldTo = Arr::only($companyRoleFieldTo, array_keys($changes));
        }

        $companyRoleFieldFrom = (new ActivityLogService)->prependKeysWithColon($companyRoleFieldFrom);
        $companyRoleFieldTo = (new ActivityLogService)->prependKeysWithColon($companyRoleFieldTo);

        if(empty($companyRoleFieldFrom) && Str::contains($logName, 'create')) {
            $companyRoleFieldFrom = $companyRoleFieldTo;
            foreach($companyRoleFieldFrom as $key => $field) {
                $companyRoleFieldFrom[$key] = null;
            }
        }

        if(in_array('permissions', array_keys($changes))){
            $addedPermissions = array_values(array_diff($companyRoleFieldTo[":permissions"], $companyRoleFieldFrom[":permissions"]));
            $removedPermissions = array_values(array_diff($companyRoleFieldFrom[":permissions"], $companyRoleFieldTo[":permissions"]));
            $companyRoleFieldFrom[":permissions"] = $removedPermissions;
            $companyRoleFieldTo[":permissions"] = $addedPermissions;
        }

        if (!empty($companyRoleFieldFrom) && !empty($companyRoleFieldTo)) {
            if (Str::contains($logName, 'edit') && !empty($changes)) {
                log_activity($logName, $companyRole, ['from' => $companyRoleFieldFrom, 'to' => $companyRoleFieldTo]);
            } elseif (Str::contains($logName, 'create')) {
                log_activity($logName, $companyRole, ['from' => $companyRoleFieldFrom, 'to' => $companyRoleFieldTo]);
            }
        }
        return true;
    }

    public static function storeRouterActivityLog($logName, $routerLocation, $changes, $routerLocationFrom, $routerLocationTo)
    {
        $routerLocationFieldFrom = Arr::only($routerLocationFrom, config("log.visible_properties.{$logName}"));
        $routerLocationFieldTo = Arr::only($routerLocationTo, config("log.visible_properties.{$logName}"));

        if(Str::contains($logName, 'edit')){
            $routerLocationFieldFrom = Arr::only($routerLocationFieldFrom, array_keys($changes));
            $routerLocationFieldTo = Arr::only($routerLocationFieldTo, array_keys($changes));
        }

        $routerLocationFieldFrom = (new ActivityLogService)->prependKeysWithColon($routerLocationFieldFrom);
        $routerLocationFieldTo = (new ActivityLogService)->prependKeysWithColon($routerLocationFieldTo);

        if(empty($routerLocationFieldFrom) && Str::contains($logName, 'create')) {
            $routerLocationFieldFrom = $routerLocationFieldTo;
            foreach($routerLocationFieldFrom as $key => $field) {
                $routerLocationFieldFrom[$key] = null;
            }
        }

        if (!empty($routerLocationFieldFrom) && !empty($routerLocationFieldTo)) {
            if (Str::contains($logName, 'edit') && !empty($changes)) {
                log_activity($logName, $routerLocation, ['from' => $routerLocationFieldFrom, 'to' => $routerLocationFieldTo]);
            }
        }
        return true;
    }

    public static function storeRouterRebootsActivityLog($logName, $routerReboot, $changes, $routerRebootFrom, $routerRebootTo)
    {
        $routerRebootFieldFrom = Arr::only($routerRebootFrom, config("log.visible_properties.{$logName}"));
        $routerRebootFieldTo = Arr::only($routerRebootTo, config("log.visible_properties.{$logName}"));

        if(Str::contains($logName, 'edit')){
            $routerRebootFieldFrom = Arr::only($routerRebootFieldFrom, array_keys($changes));
            $routerRebootFieldTo = Arr::only($routerRebootFieldTo, array_keys($changes));
        }

        $routerRebootFieldFrom = (new ActivityLogService)->prependKeysWithColon($routerRebootFieldFrom);
        $routerRebootFieldTo = (new ActivityLogService)->prependKeysWithColon($routerRebootFieldTo);

        if(empty($routerRebootFieldFrom) && Str::contains($logName, 'create')) {
            $routerRebootFieldFrom = $routerRebootFieldTo;
            foreach($routerRebootFieldFrom as $key => $field) {
                $routerRebootFieldFrom[$key] = null;
            }
        }

        if(isset($routerRebootFieldFrom[":cron_expression"]) && $routerRebootFieldFrom[":cron_expression"]){
            $routerRebootFieldFrom[":cron_expression"] = (new ActivityLogService)->translateCron($routerRebootFieldFrom[":cron_expression"]);
        }

        if(isset($routerRebootFieldTo[":cron_expression"]) && $routerRebootFieldTo[":cron_expression"]){
            $routerRebootFieldTo[":cron_expression"] = (new ActivityLogService)->translateCron($routerRebootFieldTo[":cron_expression"]);
        }

        if (!empty($routerRebootFieldFrom) && !empty($routerRebootFieldTo)) {
            if (Str::contains($logName, 'edit') && !empty($changes)) {
                log_activity($logName, $routerReboot->router, ['from' => $routerRebootFieldFrom, 'to' => $routerRebootFieldTo]);
            } elseif (Str::contains($logName, 'create')) {
                log_activity($logName, $routerReboot->router, ['from' => $routerRebootFieldFrom, 'to' => $routerRebootFieldTo]);
            }
        }
        return true;
    }

    public static function storeRouterNatActivityLog($logName, $routerNat, $changes, $routerNatFrom, $routerNatTo)
    {
        $routerNatFieldFrom = Arr::only($routerNatFrom, config("log.visible_properties.{$logName}"));
        $routerNatFieldTo = Arr::only($routerNatTo, config("log.visible_properties.{$logName}"));

        if(Str::contains($logName, 'edit')){
            $routerNatFieldFrom = Arr::only($routerNatFieldFrom, array_keys($changes));
            $routerNatFieldTo = Arr::only($routerNatFieldTo, array_keys($changes));
        }

        $routerNatFieldFrom = (new ActivityLogService)->prependKeysWithColon($routerNatFieldFrom);
        $routerNatFieldTo = (new ActivityLogService)->prependKeysWithColon($routerNatFieldTo);

        if(empty($routerNatFieldFrom) && Str::contains($logName, 'create')) {
            $routerNatFieldFrom = $routerNatFieldTo;
            foreach($routerNatFieldFrom as $key => $field) {
                $routerNatFieldFrom[$key] = null;
            }
        }

        if(isset($routerNatFieldFrom[":is_enabled"])){
            $routerNatFieldFrom[":is_enabled"] = bool_to_str($routerNatFieldFrom[":is_enabled"]);
        }

        if(isset($routerNatFieldTo[":is_enabled"])){
            $routerNatFieldTo[":is_enabled"] = bool_to_str($routerNatFieldTo[":is_enabled"]);
        }

        if (!empty($routerNatFieldFrom) && !empty($routerNatFieldTo)) {
            if (Str::contains($logName, 'edit') && !empty($changes)) {
                log_activity($logName, $routerNat->router, [':name' => $routerNat->name, 'from' => $routerNatFieldFrom, 'to' => $routerNatFieldTo]);
            }elseif (Str::contains($logName, 'create')) {
                log_activity($logName, $routerNat->router, ['from' => $routerNatFieldFrom, 'to' => $routerNatFieldTo]);
            }
        }
        return true;
    }

    public static function storeRouterZTNAActivityLog($logName, $ztna, $changes, $ztnaFrom, $ztnaTo)
    {
        $ztnaFieldFrom = Arr::only($ztnaFrom, config("log.visible_properties.{$logName}"));
        $ztnaFieldTo = Arr::only($ztnaTo, config("log.visible_properties.{$logName}"));

        if(Str::contains($logName, 'edit')){
            $ztnaFieldFrom = Arr::only($ztnaFieldFrom, array_keys($changes));
            $ztnaFieldTo = Arr::only($ztnaFieldTo, array_keys($changes));
        }

        $ztnaFieldFrom = (new ActivityLogService)->prependKeysWithColon($ztnaFieldFrom);
        $ztnaFieldTo = (new ActivityLogService)->prependKeysWithColon($ztnaFieldTo);

        if(get_class($ztna) == 'App\Modules\Ztna\Models\ZtnaRule'){
            //If it is a ZTNA rule then it will have a source and a destination and I must make them readable.
            $ztnaFieldFrom = (new ActivityLogService)->defineSourceDestinationZtnaRule($ztna, $ztnaFieldFrom);
            $ztnaFieldTo = (new ActivityLogService)->defineSourceDestinationZtnaRule($ztna, $ztnaFieldTo);
        }

        if(empty($ztnaFieldFrom) && Str::contains($logName, 'create')) {
            $ztnaFieldFrom = $ztnaFieldTo;
            foreach($ztnaFieldFrom as $key => $field) {
                $ztnaFieldFrom[$key] = null;
            }
        }

        if(isset($ztnaFieldFrom[":is_internet_enabled"])){
            $ztnaFieldFrom[":is_internet_enabled"] = bool_to_str($ztnaFieldFrom[":is_internet_enabled"]);
        }

        if(isset($ztnaFieldTo[":is_internet_enabled"])){
            $ztnaFieldTo[":is_internet_enabled"] = bool_to_str($ztnaFieldTo[":is_internet_enabled"]);
        }

        if (!empty($ztnaFieldFrom) && !empty($ztnaFieldTo)) {
            if (Str::contains($logName, 'edit') && !empty($changes)) {
                log_activity($logName, $ztna->reference, [':name' => $ztna->name, 'from' => $ztnaFieldFrom, 'to' => $ztnaFieldTo]);
            }elseif (Str::contains($logName, 'create')) {
                log_activity($logName, $ztna->reference, ['from' => $ztnaFieldFrom, 'to' => $ztnaFieldTo]);
            }
        }
        return true;
    }

    public static function storeRouterGroupActivityLog($logName, $routerGroup, $changes, $routerGroupFrom, $routerGroupTo)
    {
        $routerGroupFieldFrom = Arr::only($routerGroupFrom, config("log.visible_properties.{$logName}"));
        $routerGroupFieldTo = Arr::only($routerGroupTo, config("log.visible_properties.{$logName}"));

        if(Str::contains($logName, 'edit')){
            $routerGroupFieldFrom = Arr::only($routerGroupFieldFrom, array_keys($changes));
            $routerGroupFieldTo = Arr::only($routerGroupFieldTo, array_keys($changes));
        }

        $routerGroupFieldFrom = (new ActivityLogService)->prependKeysWithColon($routerGroupFieldFrom);
        $routerGroupFieldTo = (new ActivityLogService)->prependKeysWithColon($routerGroupFieldTo);

        if(empty($routerGroupFieldFrom) && Str::contains($logName, 'create')) {
            $routerGroupFieldFrom = $routerGroupFieldTo;
            foreach($routerGroupFieldFrom as $key => $field) {
                $routerGroupFieldFrom[$key] = null;
            }
        }

        if (!empty($routerGroupFieldFrom) && !empty($routerGroupFieldTo)) {
            if (Str::contains($logName, 'edit') && !empty($changes)) {
                log_activity($logName, $routerGroup, [':name' => $routerGroup->name,'from' => $routerGroupFieldFrom, 'to' => $routerGroupFieldTo]);
            } elseif (Str::contains($logName, 'create')) {
                log_activity($logName, $routerGroup, ['from' => $routerGroupFieldFrom, 'to' => $routerGroupFieldTo]);
            }
        }
        return true;
    }

    public static function storeNotificationActivityLog($logName, $notification, $changes, $notificationFrom, $notificationTo)
    {
        $notificationFieldFrom = $notificationFrom;
        $notificationFieldTo = $notificationTo;

        if(Str::contains($logName, 'edit')){
            $notificationFieldFrom = Arr::only($notificationFieldFrom, array_keys($changes));
            $notificationFieldTo = Arr::only($notificationFieldTo, array_keys($changes));
        }

        $notificationFieldFrom = (new ActivityLogService)->prependKeysWithColon($notificationFieldFrom);
        $notificationFieldTo = (new ActivityLogService)->prependKeysWithColon($notificationFieldTo);

        if(empty($notificationFieldFrom) && Str::contains($logName, 'create')) {
            $notificationFieldFrom = $notificationFieldTo;
            foreach($notificationFieldFrom as $key => $field) {
                $notificationFieldFrom[$key] = null;
            }
        }

        if (!empty($notificationFieldFrom) && !empty($notificationFieldTo)) {
            if (Str::contains($logName, 'edit') && !empty($changes)) {
                log_activity($logName, $notification, ['from' => $notificationFieldFrom, 'to' => $notificationFieldTo]);
            } elseif (Str::contains($logName, 'create')) {
                log_activity($logName, $notification, ['from' => $notificationFieldFrom, 'to' => $notificationFieldTo]);
            }
        }
        return true;
    }

    public static function storeWeathermapLinkActivityLog($logName, $weathermapLink, $changes, $weathermapLinkFrom, $weathermapLinkTo)
    {
        $weathermapLinkFieldFrom = Arr::only($weathermapLinkFrom, config("log.visible_properties.{$logName}"));
        $weathermapLinkFieldTo = Arr::only($weathermapLinkTo, config("log.visible_properties.{$logName}"));

        if(Str::contains($logName, 'edit')){
            $weathermapLinkFieldFrom = Arr::only($weathermapLinkFieldFrom, array_keys($changes));
            $weathermapLinkFieldTo = Arr::only($weathermapLinkFieldTo, array_keys($changes));
        }

        $weathermapLinkFieldFrom = (new ActivityLogService)->prependKeysWithColon($weathermapLinkFieldFrom);
        $weathermapLinkFieldTo = (new ActivityLogService)->prependKeysWithColon($weathermapLinkFieldTo);

        if(!empty($weathermapLinkFieldFrom)){
            if(isset($weathermapLinkFieldFrom[':source_id'])){
                $sourceFromObj = WeathermapObject::where('id', '=', $weathermapLinkFieldFrom[':source_id'])
                    ->where('weathermap_id', '=', $weathermapLink->weathermap_id)->first();
                if($sourceFromObj){
                    $weathermapLinkFieldFrom[":source"] = $sourceFromObj->name;
                }
            }

            if(isset($weathermapLinkFieldFrom[':destination_id'])){
                $dstFromObj = WeathermapObject::where('id', '=', $weathermapLinkFieldFrom[':destination_id'])
                    ->where('weathermap_id', '=', $weathermapLink->weathermap_id)->first();
                if($dstFromObj){
                    $weathermapLinkFieldFrom[":destination"] = $dstFromObj->name;
                }
            }

            unset($weathermapLinkFieldFrom[":source_id"]);
            unset($weathermapLinkFieldFrom[":destination_id"]);
        }

        if(!empty($weathermapLinkFieldTo)){
            if(isset($weathermapLinkFieldTo[':source_id'])){
                $sourceFromObj = WeathermapObject::where('id', '=', $weathermapLinkFieldTo[':source_id'])
                    ->where('weathermap_id', '=', $weathermapLink->weathermap_id)->first();
                if($sourceFromObj){
                    $weathermapLinkFieldTo[":source"] = $sourceFromObj->name;
                }
            }

            if(isset($weathermapLinkFieldTo[':destination_id'])){
                $dstFromObj = WeathermapObject::where('id', '=', $weathermapLinkFieldTo[':destination_id'])
                    ->where('weathermap_id', '=', $weathermapLink->weathermap_id)->first();
                if($dstFromObj){
                    $weathermapLinkFieldTo[":destination"] = $dstFromObj->name;
                }
            }

            unset($weathermapLinkFieldTo[":source_id"]);
            unset($weathermapLinkFieldTo[":destination_id"]);
        }

        if(isset($weathermapLinkFieldFrom[":router_ifName"])){
            try{
                list($router_id, $ifName) = explode('|', $weathermapLinkFieldFrom[":router_ifName"]);
                $weathermapLinkFieldFrom[":router_ifName"] = $ifName;
            }catch (\Exception $e){
                $weathermapLinkFieldFrom[":router_ifName"] = null;
            }
        }

        if(isset($weathermapLinkFieldTo[":router_ifName"])){
            try{
                list($router_id, $ifName) = explode('|', $weathermapLinkFieldTo[":router_ifName"]);
                $weathermapLinkFieldTo[":router_ifName"] = $ifName;
            }catch (\Exception $e){
                $weathermapLinkFieldTo[":router_ifName"] = null;
            }
        }

        if(empty($weathermapLinkFieldFrom) && Str::contains($logName, 'create')) {
            $weathermapLinkFieldFrom = $weathermapLinkFieldTo;
            foreach($weathermapLinkFieldFrom as $key => $field) {
                $weathermapLinkFieldFrom[$key] = null;
            }
        }

        if (!empty($weathermapLinkFieldFrom) && !empty($weathermapLinkFieldTo)) {
            if (Str::contains($logName, 'edit') && !empty($changes)) {
                $name = optional($weathermapLink->source)->name . " - " . optional($weathermapLink->destination)->name;
                log_activity($logName, $weathermapLink, [':name' => $name, 'from' => $weathermapLinkFieldFrom, 'to' => $weathermapLinkFieldTo]);
            }elseif (Str::contains($logName, 'create')) {
                log_activity($logName, $weathermapLink, ['from' => $weathermapLinkFieldFrom, 'to' => $weathermapLinkFieldTo]);
            }
        }
        return true;
    }
}
