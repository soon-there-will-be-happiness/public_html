<?php defined('BILLINGMASTER') or die; 

$currency = System::getMainSetting()['currency'];
?>

<style type="text/css">
.button-red {
    padding: 2px 30px 2px;
    cursor: pointer;
    display: inline-flex;
    min-height: 30px;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease 0s;
    border: 1px solid #E04265;
    background: #E04265;
    color: #ffffff;
    border-radius: 10px;
    text-align: center;
    font-size: 12px;
    text-decoration: none;
}
.button-red:hover {
    text-decoration: none;
    border: 1px solid #E04265;
    background: none;
    color: #000;
}
</style>

<div class="uk-modal-dialog uk-modal-add-elem">
    <div class="userbox modal-userbox-3">
        <form action="" method="POST" id="refund_form">
            <div class="modal-admin_top">
                <h3 class="modal-traning-title">Возврат средств покупателю</h3>
            </div>

            <div class="row-line">

                <div class="col-1-2" data-mode='1'>
		            <span data-name="msg" style="color: red;"></span>
                	<p>
                		<label>ID заказа:</label>
                       	<input type="text" name="order_id" placeholder="Введите короткое ID заказа." autocomplete="off">
	                </p>
	                <input type="hidden" name="token" value="<?=$_SESSION['admin_token']?>">
                    <input type="submit" name="refund" value="Найти заказ" class="button save button-green">
                </div>
                <div class="col-1-2" data-mode="2" style="display: none;">
                	<p>ID Заказа: <span data-name="order_id"></span> <span data-name="order_edit_url"></span></p>
                	<p>E-mail покупателя: <span data-name="email"></span></p>
                	<p>К возврату: <span data-name="amount"></span> <?=$currency?></p>
                	<input type="hidden" name="param" value="">
                    <input type="submit" name="refund" value="Подтвердить" class="button save button-green">
                    &emsp; <input type="submit" name="refund" value="Назад" class="button-red" onclick="rf_back(); return false;">
                </div>
                <div class="col-1-2">
                    <p>
                        Действия здесь не отменяют заказ в системе School-Master!<br>
                        Здесь вы вернете средства за покупку.
                    </p>
                    <p>
                     	Незабудьте отменить заказ вручную на странице "Заказы"  
                    </p>
                </div>
                <a class="button uk-modal-close uk-close modal-nav_button__close" onclick="rf_back();" href="#close">Отмена</a>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
function req(method, order_id){
    $.ajax({
        type: "POST",
        url: '/payments/dolyame/lib/ajax.php?method=' + method,
        data: {
            token: '<?=$_SESSION["admin_token"]?>',
            order_id: order_id
        },
        success: function(data) {
            if(data.comment)
                console.log(data.comment);

            if(data.result && data.result.log)
                console.log(data.result.log);
        }
    });
}

get_order = function(order_id){
    req('get', order_id);
    return 'Загружаем данные заказа #' + order_id + '...';
};

confirm_order = function(order_id){
    req('confirm', order_id);
    return 'Подтверждаем заказ #' + order_id + '...';
};

$("#refund_form").submit(function(e) {
	form_con = $("#refund_form").contents();
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/payments/dolyame/lib/ajax.php?method=refund',
        data: $(this).serialize(),
        success: function(data) {
            if(data.result && data.result.log)
            	console.log(data.result.log);

        	values = null;

        	if(data.result)
        		values = data.result;

            if(data.status == 'success'){
			    form_con.find('div[data-mode="1"]').css("display", "none");
			    form_con.find('div[data-mode="2"]').css("display", "block");

			    form_con.find('input[name="param"]').val(values['order_id']);

			    for (var key in values){
				    if (values.hasOwnProperty(key))
					    form_con.find('[data-name="' + key + '"]').html(values[key]);
				}

            }else{

			    rf_back(1);
            }

            if(data.comment){

            	if(values){
            		if(values.action){
            			if(values.action == 'back')
            				rf_back();
            		}
            		if(values.alert){
		            	alert(data.comment);
		            	return;
            		}
            	}

            	rf_msg(data.comment);
            }
        }
    });

    return false;
});

function rf_back(use){

	form_con = $("#refund_form").contents();

    form_con.find('input[name="param"]').val("");
	form_con.find('input[name="order_id"]').val("");

	if(!use){
	    form_con.find('div[data-mode="1"]').css("display", "block");
	    form_con.find('div[data-mode="2"]').css("display", "none");
    	rf_msg("");
	}
}

function rf_msg(msg, time){
	if(!time)
		time = 3;

	form_span = $("#refund_form").contents().find('[data-name="msg"]');

	form_span.html(msg);

	setTimeout(function(){
		form_span.html("");
	}, 1000 * time);
}

</script>


<? exit(); ?>