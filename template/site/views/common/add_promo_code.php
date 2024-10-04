<div style="margin-top:15px;" id="promo">
    <p><a class="promo-link" href="#"><?=System::Lang('IS_IT_PROMOCODE');?></a></p>
    <div class="promo-block" style="display: none;">
        <h6><?=System::Lang('PROMOCODE_ENTERING');?></h6>

        <div class="flex-row">
            <div class="modal-form-line max-width-200">
                <input class="small-input" type="text" name="promo" value="<?= $_GET['promo'] ?? "" ?>">
            </div>
            <div class="modal-form-submit mb-0">
                <a href="javascript:void(0)" class="btn-yellow-fz-16 d-block small-button button" data-name="apply_promo">Применить</a>
            </div>
        </div>
    </div>
</div>

<div id="promocode_msg"><?=System::Lang('PROMOCODE_APPLIED');?></div>