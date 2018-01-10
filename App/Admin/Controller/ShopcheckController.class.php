<?php
/**
 *
 * 版权所有：波波<www.bobolucy.com>
 * 作    者：波波<273719650@qq.com>
 * 日    期：2016-09-20
 * 版    本：1.0.0
 * 功能说明：商家信息控制器。
 *
 **/

namespace Admin\Controller;


use Think\Model;

class ShopcheckController extends ComController
{
    public function index(){
        $shop_id = session("shop_id");
        
        if($this->Group_id!=2 || !$shop_id){
            $this->error("您没有权限哦");
        }
        $this->display();
    }
    
    
    public function getInfo(){
        $shop_id = session("shop_id");
        
        if($this->Group_id!=2 || !$shop_id){
            $this->error("您没有权限哦");
        }
        
        $code = $_POST['code'];
        if(!$code){
            $this->error("请传入正确的code");
        }
        $where = array(
            "shop_id"=>$shop_id,
            "code"=>$code,
            "is_exchange"=>0
        );
        $orderCodeInfo = M('order_code')->where($where)->find();
        if(!$orderCodeInfo){
            $this->error("请传入正确的code,code不存在");
        }
        
        $order_id = $orderCodeInfo['order_id'];
        $orderInfo = M('order')->where(array("order_id"=>$order_id))->find();
        if(!$orderInfo || $orderInfo['order_status']!=20){
            $this->error("请传入正确的code,code无效");
        }
        
        $orderInfo['userInfo'] = M('user')->where(array("user_id"=>$orderInfo['user_id']))->find();
        
        $common = D('common');
        $info = $common->getIdInfo($orderInfo['join_id'],$orderInfo['order_type']);
        
        if($orderInfo['order_type']==1){
            $orderInfo['product_name'] = $info['attractions_name'];
        }elseif($orderInfo['order_type']==4){
            $orderInfo['product_name'] = $info['holiday_name'];
        }
        
        
        $orderInfo['product'] = $info;
        $this->success($orderInfo);
    }
    
    public function check(){
        $shop_id = session("shop_id");
        
        if($this->Group_id!=2 || !$shop_id){
            $this->error("您没有权限哦");
        }
        
        $code = $_POST['code'];
        if(!$code){
            $this->error("请传入正确的code");
        }
        $where = array(
            "shop_id"=>$shop_id,
            "code"=>$code,
            "is_exchange"=>0
        );
        $orderCodeInfo = M('order_code')->where($where)->find();
        if(!$orderCodeInfo){
            $this->error("请传入正确的code,code不存在");
        }
        
        $order_id = $orderCodeInfo['order_id'];
        $orderInfo = M('order')->where(array("order_id"=>$order_id))->find();
        if(!$orderInfo || $orderInfo['order_status']!=20){
            $this->error("请传入正确的code,code无效");
        }
        
        $model = new Model();
        $model->startTrans();
        
        $upOrder = array();
        $upOrder['order_status'] = 30;
        $upOrder['order_updated_at'] = time();
        $upOrder['order_check_at'] = time();
        
        $res = M('order')->where(array("order_id"=>$order_id))->save($upOrder);
        if(!$res){
            $model->rollback();
            $this->error("核算订单失败");
        }
        
        $upOrderCode =array(
            "is_exchange"=>1,
            "exchange_user_id"=>session("admin_id"),
            "exchange_at"=>time(),
        );
        
        $resCode = M('order_code')->where($where)->save($upOrderCode);
        if(!$resCode){
            $model->rollback();
            $this->error("核算订单失败");
        }
        
        $model->commit();
        $this->success("核销成功");
    }
}
