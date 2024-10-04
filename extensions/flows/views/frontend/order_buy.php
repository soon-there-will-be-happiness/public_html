<?defined('BILLINGMASTER') or die;
$title = !empty($params['user_title']) ? $params['user_title'] : System::Lang('FLOW');
if(isset($flows)):?>
    <li class="cart-form-input flows"><label><?=$title;?></label>
        <div class="select-wrap">
            <select name="flows">
            <?php foreach($flows as $flow){
                $count_limit = Flows::countOrdersFromFlowID($flow['flow_id']);
                if($flow['limit_users'] > 0 && $count_limit >= $flow['limit_users']) continue;
                
                if($flow['show_period'] == 1) $date = ' '.date("d.m.y", $flow['start_flow']).' - '.date("d.m.y", $flow['end_flow']);
                else $date = false;?>
                <option value="<?=$flow['flow_id']?>" data-limit="<?=$flow['limit_users'] - $count_limit;?>"><?=$flow['flow_title'];?><?=$date?></option>
            <?php }?>
            </select>
            <?php if($flows[0]['limit_users'] >= 0):?>
            <nobr class="flow-limit">Осталось мест: <span class="flow-limit-value"><?=$flows[0]['limit_users'] - Flows::countOrdersFromFlowID($flows[0]['flow_id']);?></span></nobr>
            <?php endif;?>
        </div>
    </li>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        $(document).on('change', '.flows select', function (e) {
          let $el = $("option:selected", this);
          $('.flow-limit-value').html($el.data('limit'));
        });
      });
    </script>

    <style>
        .cart-form-field .flows {
            position: relative;
        }
        .flow-limit {
            position: absolute;
            bottom: 8px;
            left: 293px;
        }
        @media screen and (max-width: 767px) {
            .flow-limit {
                position: static;
            }
        }
    </style>
<?endif;?>
