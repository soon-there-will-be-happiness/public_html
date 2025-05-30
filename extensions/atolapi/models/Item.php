<?php


namespace Atol;

class Item{
    protected $name; //наименование
    protected $price; //стоимость ед.
    protected $quantity; //количество
    protected $sum; //сумма оплаты
    protected $measurement_unit; // еденица измерения
    protected $payment_method;  // метод оплаты
    protected $payment_object; //Признак предмета расчёта

    public function getItem($data){

        $this->name = $data['name'];
        $this->price = $data['price'];
        $this->quantity = $data['quantity'];
        $this->sum = $data['sum'];
        $this->measurement_unit = $data['measurement_unit'];
        $this->payment_method = $data['payment_method'];
        $this->payment_object = 'service'; //TODO пока нет данных поставил 'service'
         // код проверки и валидации если нужен будет --- резерв
        return [
            'name'=>$this->name,
            'price'=> $this->price,
            'quantity'=>$this->quantity,
            'sum'=> $this->sum,
            'measurement_unit'=>$this->measurement_unit,
            'payment_method'=>$this->payment_method,
            'payment_object'=>$this->payment_object
        ];
    }

}

//Описание
/**Признак способа расчёта.*/
/*
Возможные значения:
 «full_prepayment» – предоплата 100%. Полная
предварительная оплата до момента передачи
предмета расчета.
 «prepayment» – предоплата. Частичная
предварительная оплата до момента передачи
предмета расчета.
 «advance» – аванс.
 «full_payment» – полный расчет. Полная
оплата, в том числе с учетом аванса
(предварительной оплаты) в момент передачи
предмета расчета.
 «partial_payment» – частичный расчет и кредит.
Частичная оплата предмета расчета в момент
его передачи с последующей оплатой в кредит.
«credit» – передача в кредит. Передача
предмета расчета без его оплаты в момент его
передачи с последующей оплатой в кредит.
 «credit_payment» – оплата кредита. Оплата
предмета расчета после его передачи с оплатой
в кредит (оплата кредита).

*/
/** Признак предмета расчёта: */
/*

 «commodity» – товар. О реализуемом товаре, за
исключением подакцизного товара
(наименование и иные сведения, описывающие
товар).
 «excise» – подакцизный товар. О реализуемом
подакцизном товаре (наименование и иные
сведения, описывающие товар).
 «job» – работа. О выполняемой работе
(наименование и иные сведения, описывающие
работу).
 «service» – услуга. Об оказываемой услуге
(наименование и иные сведения, описывающие
услугу).
 «gambling_bet» – ставка азартной игры. О
приеме ставок при осуществлении
деятельности по проведению азартных игр.
 «gambling_prize» – выигрыш азартной игры. О
выплате денежных средств в виде выигрыша
при осуществлении деятельности по
проведению азартных игр.
 «lottery» – лотерейный билет. О приеме
денежных средств при реализации лотерейных
билетов, электронных лотерейных билетов,
приеме лотерейных ставок при осуществлении
деятельности по проведению лотерей.
 «lottery_prize» – выигрыш лотереи. О выплате
денежных средств в виде выигрыша при
осуществлении деятельности по проведению
лотерей.
 «intellectual_activity» – предоставление
результатов интеллектуальной деятельности. О
предоставлении прав на использование
результатов интеллектуальной деятельности
или средств индивидуализации.
 «payment» – платеж. Об авансе, задатке,
предоплате, кредите, взносе в счет оплаты,
пени, штрафе, вознаграждении, бонусе и ином
аналогичном предмете расчета.
 «agent_commission» – агентское
вознаграждение. О вознаграждении
пользователя, являющегося платежным
агентом (субагентом), банковским платежным
агентом (субагентом), комиссионером,
поверенным или иным агентом.
 «award» – о взносе в счет оплаты пени,
штрафе, вознаграждении, бонусе и ином
аналогичном предмете расчета.
 «another» – иной предмет расчета. О предмете
расчета, не относящемуся к выше
перечисленным предметам расчета.
 «property_right» – имущественное право. О
передаче имущественных прав.
 «non-operating_gain» – внереализационный
доход. О внереализационном доходе.
 «insurance_premium» – страховые взносы. О
суммах расходов, уменьшающих сумму налога
(авансовых платежей) в соответствии с
пунктом 3.1 статьи 346.21 Налогового кодекса
Российской Федерации.
 «sales_tax» – торговый сбор. О суммах
уплаченного торгового сбора.
 «deposit» – залог. О залоге.
 «expense» – расход. О суммах произведенных
расходов в соответствии со статьей 346.16
Налогового кодекса Российской Федерации,
уменьшающих доход.
 «pension_insurance_ip» – взносы на ОПС ИП. О
страховых взносах на обязательное
пенсионное страхование, уплачиваемых ИП, не
производящими выплаты и иные
вознаграждения физическим лицам.
 «pension_insurance» – взносы на ОПС. О
страховых взносах на обязательное
пенсионное страхование, уплачиваемых
организациями и ИП, производящими выплаты
и иные вознаграждения физическим лицам.
 «medical_insurance_ip» – взносы на ОМС ИП.
О страховых взносах на обязательное
медицинское страхование, уплачиваемых ИП,
не производящими выплаты и иные
вознаграждения физическим лицам.
 «medical_insurance» – взносы на ОМС. О
страховых взносах на обязательное
медицинское страхование, уплачиваемые
организациями и ИП, производящими выплаты
и иные вознаграждения физическим лицам.
 «social_insurance» – взносы на ОСС. О
страховых взносах на обязательное социальное
страхование на случай временной
нетрудоспособности и в связи с материнством,
на обязательное социальное страхование от
несчастных случаев на производстве и
профессиональных заболеваний.
 «casino_payment» – платеж казино. О приеме и
выплате денежных средств при осуществлении
деятельности казино с использованием
обменных знаков казино, в зале игровых
автоматов.

*/