<?php defined('BILLINGMASTER') or die;

class adminSegmentFilterController extends AdminBase {


    public function actionGetCondition() {
        $cond_index = (int)$_POST['cond_index'];
        $filer_model = SegmentFilter::getFilterModel();

        if (isset($_POST['condition_type'])) {
            $type = htmlentities($_POST['condition_type']);
            echo $type ? $filer_model::getFilterHtml($_POST, $type, $cond_index) : '';
        } else {
            $logic_type = $_POST['logic_type'];
            require_once(ROOT . '/template/admin/views/segment_filter/condition.php');
        }
        return true;
    }


    public function actionGetSegmentData() {
        $filer_model = SegmentFilter::getFilterModel();
        $data = $filer_model::getSegment((int)$_POST['segment_id']);
        $data = $data ? base64_decode($data['data']) : false;

        header("Content-type: application/json; charset=utf-8");
        echo json_encode(['data' => $data]);
    }


    public function actionCheckSegment() {
        if (!isset($_POST['segment_id'])) {
            exit;
        }

        $filer_model = SegmentFilter::getFilterModel() == 'OrderFilter' ?  SegmentFilter::FILTER_TYPE_ORDERS :  SegmentFilter::FILTER_TYPE_USERS;
        $segment_id = (int)$_POST['segment_id'];
        $count = Conditions::getCountConditionsBySegmentId($segment_id, $filer_model);

        header("Content-type: application/json; charset=utf-8");
        echo json_encode(['status' => $count == 0 ? true : false]);
    }


    public function actionSaveSegment() {
        parse_str($_POST['data'], $data);
        if (!isset($data['filter'])) {
            $data['filter'] = true;
        }

        $filer_model = SegmentFilter::getFilterModel();
        $segment_id = $_POST['segment_id'] ? (int)$_POST['segment_id'] : $filer_model::getMaxSegmentId() + 1;
        $data['segment'] = $segment_id;
        $data = base64_encode(http_build_query($data));
        $segment_name = htmlentities($_POST['segment_name']);

        if (!$_POST['segment_id']) {
            $filer_model::addSegment($segment_id, $segment_name, $data);
        } else {
            $filer_model::updSegment($segment_id, $data);
        }

        header("Content-type: application/json; charset=utf-8");
        echo json_encode(['segment_id' => $segment_id]);
    }


    public function actionDelSegment($segment_id) {
        $filer_model = SegmentFilter::getFilterModel();
        $res = $filer_model::delSegment($segment_id);
        header("Content-type: application/json; charset=utf-8");
        echo json_encode(['status' => $res]);
    }


    public function actionGetAdditionalInfo() {
        $filer_model = SegmentFilter::getFilterModel();
        $value1 = (int)$_POST['val1'];
        $value2 = (int)$_POST['val2'];

        if (in_array($_POST['cond_value'], ['n_days_ago', 'n_hours_ago']) && !$value1 && !$value2) {
            exit();
        }

        echo $filer_model::getDateText2Filter($_POST['cond_value'], $value1, $value2);
}
}
