<?php


namespace app\api\controller\room;


use app\api\controller\BaseController;
use app\api\model\config\Config;
use app\api\model\place\Place;
use app\api\model\record\CheckinRecord;
use think\Request;

class Conf extends BaseController
{
    protected $configModel;
    protected $placeModel;
    protected $checkinModel;

    public function __construct(Request $request = null)
    {
        parent::_initialize();

        $this->configModel = new Config();
        $this->placeModel = new Place();
        $this->checkinModel = new CheckinRecord();

    }

    public function placeType()
    {
        $data = [
            "ROOM_TYPE" => $this->configModel->getConf($this->OrgId,"ROOM_TYPE"),
            "HOUSE_TYPE" => $this->configModel->getConf($this->OrgId,"HOUSE_TYPE"),
            "BED_TYPE" => $this->configModel->getConf($this->OrgId,"BED_TYPE"),
        ];

        app_response(200, $data);
    }


    public function placeMap()
    {
        $data = [
            'ROOM_MAP' => $this->placeModel->roomMap,
            'BED_MAP' => $this->placeModel->bedMap,
        ];

        app_response(200, $data);
    }

    public function checkinState()
    {
        $data = [
            'STATE_MAP' => $this->checkinModel->stateMap,
            'PERSON_MAP' => $this->configModel->getConf($this->OrgId,"PERSON_TYPE"),
        ];

        app_response(200, $data);
    }
}