<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php');
$now = time();?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<?php
if (!function_exists("OrderStatus")) {
    function OrderStatus($status)
    {
        switch ($status) {
            case 2 :
                $class = ' conf" title="Ручной перевод - нажмите на иконку чтобы подтвердить оплату"';
                break;

            case 0 :
                $class = ' off" title="Не оплачен"';
                break;

            case 7 :
                $class = ' send" title="Подтверждён клиентом"';
                break;

            case 9 :
                $class = ' refund" title="Возврат"';
                break;

            default :
                $class = '"';
        }

        return $class;
    }
}
?>

<div class="main">
    <div class="top-wrap">
        <h1><?=System::Lang('ORDER_LIST');?></h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank"><?=System::Lang('GO_SITE');?></a><a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>
    
    <?php if(isset($_SESSION['status']['end'])) {   
        $notice_period = 86400 * 30;
        $exp_days = round(($_SESSION['status']['end'] - $now) / 86400);
        if(isset($_SESSION['status']['end']) && $_SESSION['status']['end'] < $now + $notice_period){?>
            <div class="site-update expired_license">Доступ к обновлениям закончится через <strong><?=$exp_days;?> дней</strong>. <a target="_blank" href="https://lk.school-master.ru/buy/19?subs_id=<?=$_SESSION['map_id'];?>">Продлите чтобы продолжать получать новые возможности</a></div>
        <?php }
    }?>
    
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>Заказы</li>
    </ul>

    <span id="notification_block"></span>
    
    <div class="nav_gorizontal">
        <ul class="flex-right">
            <li>
                <a class="button-red-rounding" href="<?=$setting['script_url'];?>/admin/orders/add"><?=System::Lang('CREATE_ORDER');?></a>
            </li>
            
            <?php $params = json_decode($setting['params'], true);
            if(isset($params['crm_status']) && $params['crm_status'] == 1):?>
            <li class="nav_gorizontal__parent-wrap">
                <div class="nav_gorizontal__parent nav_gorizontal__parent-yellow">
                    <a href="javascript:void(0);" class="nav-click button-yellow-rounding">Статусы</a>
                    <span class="nav-click icon-arrow-down"></span>
                </div>
                
                <ul class="drop_down">
                    <li><a href="/admin/settings/crmstatus/add/">Добавить статус</a></li>
                    <li><a href="/admin/settings/crmstatus/">Список статусов</a></li>
                </ul>
            </li>
            <?php endif;?>
            
            <li>
                <a class="button-yellow-rounding" href="/admin/installment/map/">Рассрочки</a>
            </li>
        </ul>
    </div>

    <div class="admin_form orders-segment-filter">
        <?require_once(__DIR__.'/../segment_filter/filter.php');?>
    </div>

    <div class="admin_form admin_form--margin-top">
        <div>
            <form action="" method="POST">
                <span class="mr-20">Заказов в выборке: <strong><?=$total_order;?></strong></span>
                <?if($total_order):?>
                    <input class="csv__link"  type="submit" name="load_csv" value="выгрузить в csv">
                <?endif;?>
            </form>
        </div>

        <?php $orders_info = (isset($params['show_orders_statistic']) && $params['show_orders_statistic'] == 2)
        || isset($_GET['filter']) ? Order::getOrdersInfoWithConditions($conditions) : null;

        if($orders_info):?>
            <div class="orders-info">
                <div class="orders-info-item">
                    <div class="orders-info-item-left"><?=$orders_info['paid_orders']['count'];?></div>
                    <div class="orders-info-item-right">
                        <div><?=System::addTermination2($orders_info['paid_orders']['count'], 'платн[TRMNT]').' '.System::addTermination($orders_info['paid_orders']['count'], 'заказ[TRMNT]');?></div>
                        <div><?=number_format($orders_info['paid_orders']['order_sum'], 0, '.','.')." {$setting['currency']}";?></div>
                    </div>
                </div>

                <div class="orders-info-item">
                    <div class="orders-info-item-left"><?=$orders_info['paid']['count'];?></div>
                    <div class="orders-info-item-right">
                        <div>оплачено (<?=$total_order ? ceil($orders_info['paid']['count'] / $total_order * 100) : 0;?>%)</div>
                        <div><?=number_format((int)$orders_info['paid']['order_sum'], 0, '.','.')." {$setting['currency']}";?></div>
                    </div>
                </div>

                <div class="orders-info-item">
                    <div class="orders-info-item-left"><?=$orders_info['actual']['count'];?></div>
                    <div class="orders-info-item-right">
                        <div><?=System::addTermination2($orders_info['actual']['count'], 'актуальн[TRMNT]');?></div>
                        <div><?=number_format($orders_info['actual']['order_sum'], 0, '.','.')." {$setting['currency']}";?></div>
                    </div>
                </div>
            </div>
        <?endif;?>
    </div>
    <?php if(isset($_GET['success'])):?>
        <div class="admin_message">Успешно!</div>
    <?php endif;?>
    
    <div class="admin_form admin_form--margin-top">
        <div class="overflow-container">
            <table class="table table-sort ordersTable" style="width: 100%; max-width: 100%; word-wrap: break-word;">
                <thead>
                    <tr>
                        <th class="text-left" id="OrderNumber" style="max-width: 80px;"><input type="text" id="OrderNumberInput" placeholder="Номер" value=""></th>
                        <th class="text-left" id="ClientName"  style="max-width: 163px !important;"><input type="text" id="OrderClientInput" placeholder="Пользователь" value=""></th>
                        <th class="text-left" id="OrderProduct" style=""><select id="OrderProductInput" placeholder="Продукт"></select></th>
                        <th style="max-width: 70px;"><?=System::Lang('SUMMCLEAN');?></th>
                        <th style="max-width: 70px;" id="OrderStatus"><div bis_skin_checked="1"><?=System::Lang('STATUS');?></div></th>
                    </tr>
                </thead>

                <tbody id="OrderTableBody">
                    <?php if(!empty($order_list)):
                        foreach($order_list as $order):?>
                            <?php include (ROOT."/template/admin/views/orders/order_card.php"); ?>
                        <?php endforeach;
                    endif;?>
                </tbody>
            </table>
            <div id="loadingimage" class="hidden" style="height: 200px; background-image: url('/template/admin/images/spinner2.gif'); background-position: center; background-repeat: no-repeat;"></div>
            <div align="center" id="fastfiltermess" style="padding: 15px; min-height: 300px;"></div>
        </div>
    </div>

    <?php if($is_pagination == true) echo $pagination->get();?>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<script>
    let searchData = {
        "id": "",
        "client": "",
        "status": " ",
        "product": "0",
    };
    let oldTableDataHTML = document.getElementById('OrderTableBody').innerHTML;
    let productsOptions = `<option value="0">Продукт</option><?php $products = Product::getProductListOnlySelect(); foreach ($products as $product) { ?><option value="<?=$product['product_id']?>"><?=$product['product_name']?></option><?php } ?>`;

    document.addEventListener("DOMContentLoaded", function () {
        let tabs = [];

        let timerNum;
        let timerName;
        let timerProduct;
        let timerStatus;

        tabs["OrderNumber"] = document.getElementById("OrderNumber");
        let numInput = document.getElementById("OrderNumberInput");
        tabs["ClientName"] = document.getElementById("ClientName");
        let nameInput = document.getElementById("OrderClientInput");
        tabs["OrderProduct"] = document.getElementById("OrderProduct");
        let productInput = document.getElementById("OrderProductInput");
        tabs["OrderStatus"] = document.getElementById("OrderStatus");

        //Айди заказа
            numInput.setAttribute("value", searchData.id);
            numInput.addEventListener("click", function (e) {
                e.stopPropagation();
            });
            numInput.addEventListener("input", function (e) {
                if (isNaN(parseInt(e.target.value))) {
                    e.target.value = "";
                } else {
                    e.target.value = parseInt(e.target.value);
                }

                searchData.id = e.target.value;
            });
            numInput.addEventListener("mouseover", function (e) {
                numInput.focus();
            });
        numInput.addEventListener('mouseout', function () {
            sendRequest();
            filterIsZero();
            console.log(searchData)
        });

        //Имя клиента
        nameInput.setAttribute("value", searchData.client);
        nameInput.addEventListener("click", function (e) {
            e.stopPropagation();
        });
        nameInput.addEventListener("mouseover", function (e) {
            nameInput.focus();
        });
        nameInput.addEventListener("input", function (e) {
            searchData.client = e.target.value;
        });

        nameInput.addEventListener('mouseout', clientNameReq);
        function clientNameReq () {
            sendRequest();
            filterIsZero();
            console.log(searchData)
        }

        //Продукт заказа

        productInput.classList.add("selectwidth100");
        productInput.innerHTML = productsOptions;
        productInput.setAttribute("value", searchData.product);

        productInput.addEventListener("click", function (e) {
            e.stopPropagation();
        });
        productInput.addEventListener("input", function (e) {
            searchData.product = e.target.value;
        });
        productInput.addEventListener("mouseout", function (e) {
            sendRequest();
        });
        productInput.addEventListener("mouseout", function (e) {
            productInput.focus();
        });

        productInput.addEventListener('mouseout', function () {
            sendRequest();
            filterIsZero();
            console.log(searchData)
        });

        /*//Статус заказа
        tabs["OrderStatus"].addEventListener('mouseover', function () {
            if (tabs.OrderStatus.innerHTML !== '<div bis_skin_checked="1">Статус</div>') {
                return true;
            }

            let statusInput = document.createElement("select");
            statusInput.setAttribute("name", "OrderNumber");
            statusInput.setAttribute("title", "Статус заказа");
            statusInput.setAttribute("placeholder", "Статус заказа");
            statusInput.innerHTML = `<option value=" ">Любой</option><option value="0">Не оплачен</option><option value="1">Оплачен</option><option value="2">Требует проверки</option><option value="3">На рассмотрении (требуется подтверждение)</option><option value="4">Отклонён</option><option value="5">Ожидаем платёж по рассрочке</option><option value="7">Подтверждение доставки по емейл</option><option value="9">Возврат</option><option value="97">Ожидает возврата клиенту</option><option value="98">Ложный</option><option value="99">Отменён</option>`;

            tabs.OrderStatus.replaceChild(statusInput, tabs.OrderStatus.lastChild);
            statusInput.focus();
            statusInput.addEventListener("input", function (e) {
                searchData.status = e.target.value;
            });
        });
        tabs["OrderStatus"].addEventListener('mouseout', function () {
            function closeOrderStatus() {
                sendRequest();
                if (searchData.status !== " ") {
                    return true;
                }
                tabs.OrderStatus.innerHTML = '<div bis_skin_checked="1">Статус</div>';
                console.log(searchData)
            }
            clearTimeout(timerStatus);
            timerStatus = setTimeout(closeOrderStatus, 500);
        });*/


    });

    function filterIsZero() {
        if (searchData.id !== "") {
            return true;
        }
        if (searchData.client !== "") {
            return true;
        }
        if (searchData.product !== "0") {
            return true;
        }
        document.getElementById('OrderTableBody').innerHTML = oldTableDataHTML;
        document.getElementById('fastfiltermess').innerHTML = "";
        document.querySelector('.pagination').classList.remove('hidden');
    }
    let lastUrl = "/admin/orders/fastfilter?";
    async function sendRequest() {
        let url = "/admin/orders/fastfilter?";
        if (searchData.id !== "") {
            url = url + 'id=' + searchData.id + "&";
        }
        if (searchData.client !== "") {
            url = url + 'client=' + searchData.client + "&";
        }
        if (searchData.product !== "0") {
            url = url + 'product=' + searchData.product + "&";
        }
        if (searchData.status !== " ") {
            url = url + 'status=' + searchData.status + "&";
        }

        if (url === "/admin/orders/fastfilter?" || url === lastUrl) {
            return false;
        }
        lastUrl = url;
        document.getElementById('OrderTableBody').innerHTML = "";
        document.getElementById('loadingimage').classList.remove("hidden");//Анимация загрузки

        let response = await fetch(url);
        let status = response.status;

        document.getElementById('loadingimage').classList.add("hidden");
        if (status !== 200) {
            if (status === 404) {
                let mess = await response.json();
                document.getElementById('OrderTableBody').innerHTML = "";
                document.getElementById('fastfiltermess').innerHTML = "<div>" + mess.message + "</div>";
                document.querySelector('.pagination').classList.add('hidden');
                console.log(mess.message);
            }
            return false;
        }

        let result = await response.text();
        document.getElementById('OrderTableBody').innerHTML = result;
        document.getElementById('fastfiltermess').innerHTML = "";
        document.querySelector('.pagination').classList.add('hidden');
    }
</script>
<style>
    .ordersTable thead {
        height: 45px;
    }
    .ordersTable thead th input {
        width: 100%;
        height: 35px;
    }
    .ordersTable thead th select {
        padding: 0 8px;
        width: 100%;
        height: 35px;
    }
    .selectwidth100 {
        padding: 0 8px;
    }
    .ordersTable thead th input::placeholder {
        font-size: 14px;
        color: black;
        opacity: 1;
    }
    .table.dataTable thead .sorting_asc, .table.dataTable thead .sorting_desc, .table.dataTable thead .sorting{
        background-position: 100% 30% !important;
    }
</style>
<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
  jQuery('.datetimepicker').datetimepicker({
    format:'d.m.Y H:i',
    lang:'ru',
    firstDay: 1,
  });
</script>
</body>
</html>
