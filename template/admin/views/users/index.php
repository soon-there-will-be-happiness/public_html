<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1><?=System::Lang('USER_LIST'.$role);?></h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>Пользователи</li>
    </ul>

    <div class="nav_gorizontal">
        <ul class="nav_gorizontal__ul flex-right">
            <li class="nav_gorizontal__parent-wrap">
                <div class="nav_gorizontal__parent nav_gorizontal__parent-red">
                    <a href="/admin/users/create/" class="nav-click button-red-rounding">Добавить пользователя</a>
                    <span class="nav-click icon-arrow-down"></span>
                </div>

                <ul class="drop_down">
                    <li><a href="/admin/users/custom-fields/">Кастомные поля</a></li>
                </ul>
            </li>

            <li class="nav_gorizontal__parent-wrap">
                <div class="nav_gorizontal__parent nav_gorizontal__parent-yellow">
                    <a href="javascript:void(0);" class="nav-click button-yellow-rounding">Группы</a>
                    <span class="nav-click icon-arrow-down"></span>
                </div>

                <ul class="drop_down">
                    <li><a href="/admin/usergroups/add/">Создать группу</a></li>
                    <li><a href="/admin/usergroups/">Список групп</a></li>
                </ul>
            </li>

            <li class="nav_gorizontal__parent-wrap">
                <div class="nav_gorizontal__parent nav_gorizontal__parent-yellow">
                    <a href="javascript:void(0);" class="nav-click button-yellow-rounding">Действие</a>
                    <span class="nav-click icon-arrow-down"></span>
                </div>

                <ul class="drop_down">
                    <li><a href="/admin/users/export/">Экспорт →</a></li>
                    <li><a href="/admin/users/import/">Импорт ←</a></li>
                    <li><a onclick="return confirm('Вы уверены? Все пользователи смогут воспользоваться авторизацией по ссылке, без ввода пароля. Старые ссылки станут не действительными')" href="<?=$setting['script_url'];?>/admin/users/gentokens?token=<?=$_SESSION['admin_token'];?>" title="Создать всем пользователям токены для входа по ссылке">Создать токены </a></li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="admin_form users-segment-filter">
        <?require_once(__DIR__.'/../segment_filter/filter.php');?>
    </div>

    <div class="admin_form admin_form--margin-top">
        <div>
            <form action="" method="POST">
                <span class="mr-20">Пользователей в выборке: <strong><?=$total_users;?></strong></span>
                <?if($total_users):?>
                    <input class="csv__link"  type="submit" name="load_csv" value="выгрузить в csv">
                <?endif;?>
            </form>
        </div>
    </div>

    <?if(isset($_GET['success'])):?>
        <div class="admin_message">Успешно!</div>
    <?php endif;?>

    <div class="admin_form admin_form--margin-top">
        <div class="overflow-container">
            <table class="table usersTable">
                <tr>
                    <th>ID</th>
                    <th class="text-left" id="ClientName"><input type="text" id="OrderClientInput" placeholder="Пользователь" value="" title="Id/Имя/Фамилия/Отчество/email пользователя для поиска"></th>
                    <th class="text-left">Дата регистрации</th>
                    <th class="td-last">Заказы</th>
                    <th class="td-last">Покупки</th>
                </tr>
                <tbody id="OrderTableBody">
                    <?php if ($users):
                        foreach($users as $user):?>
                            <?php include (ROOT.'/template/admin/views/users/user_card.php') ?>
                        <?php endforeach;
                    else:
                        echo 'No users';
                    endif;?>
                </tbody>
            </table>
            <div id="loadingimage" class="hidden" style="height: 200px; background-image: url('/template/admin/images/spinner2.gif'); background-position: center; background-repeat: no-repeat;"></div>
            <div align="center" id="fastfiltermess" style="padding: 15px; min-height: 0px;"></div>
        </div>
    </div>

    <?php if(isset($is_pagination) && $is_pagination == true) {
        echo $pagination->get();
    }?>

    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
<script>
    let searchData = {
        "client": "",
    };
    let oldTableDataHTML = document.getElementById('OrderTableBody').innerHTML;
    let productsOptions = `<option value="0">Продукт</option><?php $products = Product::getProductListOnlySelect(); foreach ($products as $product) { ?><option value="<?=$product['product_id']?>"><?=$product['product_name']?></option><?php } ?>`;

    document.addEventListener("DOMContentLoaded", function () {
        let tabs = [];

        let timerNum;
        let timerName;
        let timerProduct;
        let timerStatus;

        tabs["ClientName"] = document.getElementById("ClientName");
        let nameInput = document.getElementById("OrderClientInput");


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
        nameInput.addEventListener("keyup", function (event) {
            if(event.keyCode == 13){
                clientNameReq();
            }
        });
        nameInput.addEventListener('mouseout', clientNameReq);
        function clientNameReq () {
            sendRequest();
            filterIsZero();
            console.log(searchData)
        }
    });

    function filterIsZero() {
        if (searchData.client !== "") {
            return true;
        }
        document.getElementById('OrderTableBody').innerHTML = oldTableDataHTML;
        document.getElementById('fastfiltermess').innerHTML = "";
        document.querySelector('.pagination').classList.remove('hidden');
    }
    let lastUrl = "/admin/users/fastfilter?";
    async function sendRequest() {
        let url = "/admin/users/fastfilter?";
        if (searchData.client !== "") {
            url = url + 'client=' + searchData.client + "&";
        }

        if (url === "/admin/users/fastfilter?" || url === lastUrl) {
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
    .usersTable tr th input {
        width: 100%;
        height: 35px;
    }
    .usersTable tr input::placeholder {
        font-size: 14px;
        color: black;
        opacity: 1;
    }
</style>
<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
</body>
</html>