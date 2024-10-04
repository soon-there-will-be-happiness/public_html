<?php

namespace Atol;


class Client{

    public function getClient($data){

    $client['error'] = 'none';

        if ($data != null) {
            $validate_name = $this->checkClientName($data['name']);
            $validate_email =$this->checkClientEmail($data['email']);
                if($validate_name != 'ok')
                    $client['error'] = $validate_name ;
                elseif($validate_email != 'ok')
                    $client['error'] = $validate_email ;
                else
                    $client['error'] = 'none';


            if($client['error'] == 'none'){
                $client = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'inn' => $data['inn']
                ];
            }
            return $client;
        }

    }


    public function checkClientName($name){ //валидация данных  name

        if($name != null ){
            if(strlen($name) < 10)
                return 'Слишком короткие данные покупателя';
            elseif (strlen($name) > 255 )
                return 'Слишком большие данные покупателя';
            else
                return  'ok';
        }
        else
            return 'Нет данных имени покупателя!';

    }


    public function checkClientEmail($email){ //валидация данных  email

        if($email != null ){
            if(strlen($email) < 7)
                return 'Неверный  email покупателя';
            elseif (strlen($email) > 64 )
                return 'Неверный  email покупателя';
            else
                return  'ok';
        }
        else
            return 'Нет данных email имени покупателя!';

    }

    public function checkClientPhone($phone){ //валидация данных  email
        //тут если необходимо - валидация
    }

    public function checkClientInn($inn){ //валидация данных  email
        //тут если необходимо - валидация
    }

}

