<?php defined('BILLINGMASTER') or die;


class redirectController extends baseController {
    
    
    public function actionGo($id)
    {
        $id = intval($id);
        $setting = System::getSetting();
        $now = time();
        $redirect = Redirect::redirectData($id);

        if ($redirect) {
            // ограничение переходов
            if($redirect['limit_hits'] != 0 && $redirect['hits'] >= $redirect['limit_hits']) {
                System::redirectUrl($redirect['alt_url']);
            }
            
            // ограничение времени
            if ($redirect['end_date'] < $now) {
                System::redirectUrl($redirect['alt_url']);
            }

            System::redirectUrl($redirect['url']);
        } else {
            ErrorPage::return404();
        }
        return true;
    }
}