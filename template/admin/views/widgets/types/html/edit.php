<?php defined('BILLINGMASTER') or die; ?>
<div class="row-line">
    <div class="col-1-1 mb-0">
        <h4>Содержимое</h4>
    </div>

    <div class="col-1-1">
        <p><label><?=System::Lang('YOUR_HTML');?>:</label>
            <textarea class="editor" name="widget[params][code_html]" rows="6" cols="45"><?=isset($params['params']['code_html']) ? $params['params']['code_html'] : '';?></textarea>
        </p>

        <p><label><?=System::Lang('YOUR_HTML_PHP');?>:</label>
            <textarea name="widget[params][code]" rows="6" cols="45"><?php echo $params['params']['code'];?></textarea>
        </p>
    </div>
</div>