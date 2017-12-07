<?php

namespace app\admin\controller;

use app\admin\model\ErrorCode;
use \app\common\model\AuthRule as AuthRuleModel;

/**
 * 权限相关
 */
class AuthRule extends BaseCheckUser
{

    /**
     * 列表
     */
    public function index()
    {

        $where = [];
        $order = 'id ASC';
        $status = request()->get('status', '');
        if ($status !== ''){
            $where[] = ['status','=',intval($status)];
            $order = '';
        }
        $name = request()->get('name', '');
        if (!empty($name)){
            $where[] = ['name','like',$name . '%'];
            $order = '';
        }
        $lists = AuthRuleModel::where($where)
            ->field('id,pid,name,title,status,condition,listorder')
            ->order($order)
            ->select();

        $tree_list = AuthRuleModel::cateMerge($lists,'id','pid',0);

        return json($tree_list);

    }

    /**
     * 添加
     */
    public function save(){
        $data = $this->request->post();
        if (empty($data['name']) || empty($data['status'])){
            $res = [];
            $res['errcode'] = ErrorCode::$HTTP_METHOD_NOT_ALLOWED;
            $res['errmsg'] = 'Method Not Allowed';
            return json($res);
        }
        $name = strip_tags($data['name']);
        // 菜单模型
        $info = AuthRuleModel::where('name',$name)
            ->field('name')
            ->find();
        if ($info){
            $res = [];
            $res['errcode'] = ErrorCode::$DATA_REPEAT;
            $res['errmsg'] = '权限已经存在';
            return json($res);
        }

        $now_time = time();
        $status = isset($data['status']) ? $data['status'] : 0;
        $pid = isset($data['pid']) ? $data['pid'] : 0;
        if ($pid){
            $info = AuthRuleModel::where('id',$pid)
                ->field('id')
                ->find();
            if (!$info){
                $res = [];
                $res['errcode'] = ErrorCode::$NOT_NETWORK;
                $res['errmsg'] = '网络繁忙';
                return json($res);
            }
        }
        $AuthRuleModel = new AuthRuleModel();
        $AuthRuleModel->pid = $pid;
        $AuthRuleModel->name = $name;
        $AuthRuleModel->title = isset($data['title']) ? $data['title'] : '';
        $AuthRuleModel->status = $status;
        $AuthRuleModel->condition = isset($data['condition']) ? $data['condition'] : '';
        $AuthRuleModel->listorder = isset($data['listorder']) ? strip_tags($data['listorder']) : 0;
        $AuthRuleModel->create_time = $now_time;
        $AuthRuleModel->update_time = $now_time;
        $result = $AuthRuleModel->save();

        if (!$result){
            $res = [];
            $res['errcode'] = ErrorCode::$NOT_NETWORK;
            $res['errmsg'] = '网络繁忙！';
            return json($res);
        }

        $res['id'] = $AuthRuleModel->getLastInsID();
        $res['pid'] = $AuthRuleModel->pid;
        $res['name'] = $AuthRuleModel->name;
        $res['title'] = $AuthRuleModel->title;
        $res['status'] = $AuthRuleModel->status;
        $res['condition'] = $AuthRuleModel->condition;
        $res['listorder'] = $AuthRuleModel->listorder;
        $res['create_time'] = $AuthRuleModel->create_time;
        $res['update_time'] = $AuthRuleModel->update_time;

        return json($res);
    }

    /**
     * 编辑
     */
    public function edit(){
        $data = $this->request->post();
        if (empty($data['id']) || empty($data['name'])){
            $res = [];
            $res['errcode'] = ErrorCode::$HTTP_METHOD_NOT_ALLOWED;
            $res['errmsg'] = 'Method Not Allowed';
            return json($res);
        }
        $id = $data['id'];
        $name = strip_tags($data['name']);
        // 模型
        $AuthRuleModel = AuthRuleModel::where('id',$id)
            ->field('id')
            ->find();
        if (!$AuthRuleModel){
            $res = [];
            $res['errcode'] = ErrorCode::$DATA_NOT;
            $res['errmsg'] = '角色不存在';
            return json($res);
        }

        $info = AuthRuleModel::where('name',$name)
            ->field('id,pid')
            ->find();
        // 判断名称 是否重名，剔除自己
        if (!empty($info['id']) && $info['id'] != $id){
            $res = [];
            $res['errcode'] = ErrorCode::$DATA_REPEAT;
            $res['errmsg'] = '权限名称已存在';
            return json($res);
        }
        $pid = isset($data['pid']) ? $data['pid'] : 0;
        if ($info['id'] == $pid){
            $res = [];
            $res['errcode'] = ErrorCode::$NOT_NETWORK;
            $res['errmsg'] = '不能把自身作为父级';
            return json($res);
        }

        $pid1 = AuthRuleModel::where('pid',$id)->value('pid');

        // 判断父级是否存在
        if ($pid){
            $info = AuthRuleModel::where('id',$pid)
                ->field('id')
                ->find();
            if (!$info){
                $res = [];
                $res['errcode'] = ErrorCode::$NOT_NETWORK;
                $res['errmsg'] = '网络繁忙';
                return json($res);
            }
        }

        $status = isset($data['status']) ? $data['status'] : 0;
        $AuthRuleModel->pid = $pid;
        $AuthRuleModel->name = $name;
        $AuthRuleModel->title = isset($data['title']) ? $data['title'] : '';
        $AuthRuleModel->status = $status;
        $AuthRuleModel->condition = isset($data['condition']) ? $data['condition'] : '';
        $AuthRuleModel->listorder = isset($data['listorder']) ? strip_tags($data['listorder']) : 0;
        $AuthRuleModel->update_time = time();
        $result = $AuthRuleModel->save();

        if (!$result){
            $res = [];
            $res['errcode'] = ErrorCode::$DATA_CHANGE;
            $res['errmsg'] = '数据没有任何更改';
            return json($res);
        }


        return 'SUCCESS';
    }


    /**
     * 删除
     */
    public function delete(){
        $id = request()->post('id/d');
        if (empty($id)){
            $res = [];
            $res['errcode'] = ErrorCode::$HTTP_METHOD_NOT_ALLOWED;
            $res['errmsg'] = 'Method Not Allowed';
            return json($res);
        }
        if (!RoleModel::where('id',$id)->delete()){
            $res = [];
            $res['errcode'] = ErrorCode::$NOT_NETWORK;
            $res['errmsg'] = '网络繁忙！';
            return json($res);
        }

        return 'SUCCESS';

    }

}