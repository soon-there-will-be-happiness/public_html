<?php defined('BILLINGMASTER') or die;


class Access {
    
    // ПРИНИМАЕТ ID юзера, данные сущнсоти: ТИП доступа, ГРУППЫ и ПЛАНЫ
    // СРАВНИВАЕТ группы юзера с группами сущности и возвращает true или false
    public static function getAccesstoUser($user_id, $access_type, $groups, $planes)
    {
        $access = false;
        // По группам
        if($access_type == 1){
            
            $user_groups = User::getGroupByUser($user_id);
            $groups_arr = json_decode($groups, true);
            
            if ($user_groups && !empty($groups_arr)) {
                foreach($user_groups as $group) {
                    if (in_array($group, $groups_arr)) $access = true;
                }
            } 
        }
        
        if($access_type == 2){
            
            $membership = System::CheckExtensension('membership', 1);
            if($membership){
                
                $user_planes = Member::getPlanesByUser($user_id);
                $planes_arr = json_decode($planes, true);
                
                if ($user_planes && !empty($planes_arr)) {
                    foreach($user_planes as $plane) {
                        if (in_array($plane, $planes_arr)) $access = true;
                    }
                }
            }
        }
        
        return $access;
        
    }
    
}