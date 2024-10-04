<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
  <div class="top-wrap">
    <h1>Создать категорию</h1>
    <div class="logout">
        <a href="/" target="_blank">Перейти на сайт</a><a href="/admin/logout" class="red">Выход</a>
    </div>
  </div>
  <ul class="breadcrumb">
    <li>
      <a href="/admin">Дашбоард</a>
    </li>
    <li><a href="/admin/settings/currency/">Список валют</a></li>
    <li>Добавить валюту</li>
  </ul>
    <form action="" method="POST" enctype="multipart/form-data">

      <div class="admin_top admin_top-flex">
        <h3 class="traning-title">Создать валюту</h3>
        <ul class="nav_button">
          <li><input type="submit" name="add_currency" value="Сохранить" class="button save button-white font-bold"></li>
          <li class="nav_button__last"><a class="button red-link" href="/admin/settings/currency/">Закрыть</a></li>
        </ul>
      </div>
    
        <div class="admin_form">
           <div class="row-line">
                <div class="col-1-2">
                    <h4>Основное</h4>
                    <p class="width-100"><label>Название: </label><input type="text" name="name" placeholder="Название валюты" required="required"></p>

                    <div class="width-100"><label>Статус: </label>
                       <div class="select-wrap">
                            <select name="status">
                                <option value="1">Включен</option>
                                <option value="0">Отключен</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                </div>

                <div class="col-1-2">
                    <h4>Дополнительно</h4>
                    <p class="width-100"><label>Написание:</label><input type="text" name="simbol"></p>
                    <p class="width-100"><label>Код валюты:</label><input type="text" name="code"></p>
                    <p class="width-100"><label>Курс к основной валюте:</label><input type="text" name="tax"></p>
                </div>
</div>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>