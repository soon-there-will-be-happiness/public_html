<?defined('BILLINGMASTER') or die;?>

<h1><?=System::Lang('AUTHOR_CAB');?></h1>
<?if(isset($_GET['success_reg'])):?>
    <div class="success_message">Вы зарегистрированы в партнёрской программе</div>
<?endif;

if(isset($_GET['success'])):?>
    <div class="success_message">Сохранено!</div>
<?endif;?>

<div class="tabs">
    <ul>
        <li><?=System::Lang('SUMMARY');?></li>
        <li><?=System::Lang('ACCRUALS');?></li>
        <li><?=System::Lang('REQUISITES');?></li>
    </ul>

    <div class="userbox usertabs">
        <!--  Основное  -->
        <div>
            <div class="total-money">
                <h4><?=System::Lang('TOTAL_EARNED');?> <?if($total['SUM(summ)'] > 0) echo $total['SUM(summ)']; else echo 0;?> <?=$this->settings['currency'];?></h4>
                <p><?=System::Lang('PAID_OUT');?> <?if($total['SUM(pay)'] > 0) echo $total['SUM(pay)']; else echo 0;?> <?=$this->settings['currency'];?></p>
                <p><?=System::Lang('OWNED');?> <?if($total['SUM(summ)'] - $total['SUM(pay)'] > 0) echo $total['SUM(summ)'] - $total['SUM(pay)']; else echo 0;?> <?=$this->settings['currency'];?></p>
            </div>
        </div>

        <!--  Начисления  -->
        <div>
            <p><?=System::Lang('LAST_FIFTY_ACCUALS');?></p>
            <div class="table-responsive">
                <table class="usertable">
                    <tr>
                        <th><?=System::Lang('DATE');?></th>
                        <th><?=System::Lang('PRODUCT');?></th>
                        <th><?=System::Lang('EMAIL');?></th>
                        <th><?=System::Lang('ORDER_ID');?></th>
                        <th><?=System::Lang('SUMM');?></th>
                    </tr>

                    <?if($transacts):
                        foreach($transacts as $action):
                            $name = Product::getProductName($action['product_id']);?>
                            <tr>
                                <td><?=date("d.m.Y H:i:s", $action['date']);?></td>
                                <td><?=$name['product_name']?></td>
                                <td><?=Order::getEmailByOrder($action['order_id']);?></td>
                                <td><?=$action['order_id'];?></td>
                                <td><?=$action['summ'];?> <?=$this->settings['currency'];?></td>
                            </tr>
                        <?endforeach;
                    endif;?>
                </table>
            </div>
        </div>

        <!--  Реквизиты -->
        <div>
            <div class="requisites">
                <?$req = unserialize($req['requsits']);
                $reqs = explode("\r\n", $params['params']['req']);?>

                <form action="" method="POST">
                    <?foreach($reqs as $req_item):
                        $req_item = explode("=", $req_item);
                        $f = !empty($req) && array_key_exists($req_item[0], $req) ? true : false;

                        if($req_item[0] != 'rs'):?>
                            <div class="h4 requisites__subtitle"><?=$req_item[1];?></div>
                            <div class="modal-form-line">
                                <input placeholder="Номер кошелька" type="text" name="req[<?=$req_item[0];?>]" value="<?=$f ? $req[$req_item[0]] : '';?>">
                            </div>
                        <?else:?>
                            <div class="h4 requisites__subtitle"><?=$req_item[1];?></div>
                            <div class="modal-form-line">
                                <input placeholder="Номер счета" type="text" name="req[rs][rs]" value="<?=$f ? $req[$req_item[0]]['rs'] : '';?>">
                            </div>

                            <div class="modal-form-line">
                                <input placeholder="Название организации" type="text" name="req[<?=$req_item[0];?>][name]" value="<?=$f ? $req[$req_item[0]]['name'] : '';?>">
                            </div>

                            <div class="modal-form-line">
                                <input placeholder="БИК" type="text" name="req[<?=$req_item[0];?>][bik]" value="<?=$f ? $req[$req_item[0]]['bik'] : '';?>">
                            </div>

                            <div class="modal-form-line">
                                <input placeholder="ИНН" type="text" name="req[<?=$req_item[0];?>][itn]" value="<?=$f ? $req[$req_item[0]]['itn'] : '';?>">
                            </div>
                        <?endif;
                    endforeach; ?>
                    <div class="requisites__button"><input type="submit" class="button btn-blue" value="Сохранить" name="save_req"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function() {
    $('input[name*="card"]').attr('placeholder', 'Номер карты');
  });
</script>