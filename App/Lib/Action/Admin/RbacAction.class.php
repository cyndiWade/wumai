<?php
/**
 *	管理员权限控制管理
 *
 */
class RbacAction extends AdminBaseAction {
	
	private $MODULE = '系统管理';
	
	/**
	 * 构造方法
	 */
	public function __construct() {
		parent::__construct();
		$this->assign('MODULE_NAME',$this->MODULE);
	}
	
	/**
	 * 节点管理
	 */
	public function rbac_node () {
		
		$pid = $this->_get('pid');
		$pid2 = $this->_get('pid2');
		$Node = D('Node');
		
		$node1 = $Node->where(array('level'=>1))->select();									//第一级节点
		$node2 = $Node->where(array('level'=>2,'pid'=>$pid))->select();				//第二级节点
		$node3 = $Node->where(array('level'=>3,'pid'=>$pid2))->select();				//第三级节点
		
		$this->assign('pid',$pid);
		$this->assign('pid2',$pid2);
		$this->assign('node1',$node1);
		$this->assign('node2',$node2);
		$this->assign('node3',$node3);
		$this->display();
	}
	
	
	/**
	 * 编辑节点
	 */
	public function node_edit() {
		
		$type = $this->_get('type');			//操作类型
		$pid = $this->_get('pid');				//节点pid
		$level = $this->_get('level');			//节点等级
		$Node = D('Node');						//节点表
		
		if ($type == 'add') {							//添加
			if ($this->isPost()) {
				$Node->create();
				$Node->add() ? $this->success('添加成功',$_POST['PREV_URL']) : $this->error('添加失败');
				exit;
			}
		
		} else if ($type == 'update') {			//修改
			if ($this->isPost()) {
				$data['name'] = $this->_post('name');
				$data['title'] = $this->_post('title');
				$Node->where(array('id'=>$pid))->save($data) ? $this->success('修改成功',$_POST['PREV_URL']) : $this->error('没有数据被修改');
				exit;
			}
			$nodeFind = $Node->where(array('id'=>$pid))->find();		//取得当前用户信息
			$this->assign('nodeFind',$nodeFind);
		} else if ($type == 'delete') {			//删除
				
			$Node->where(array('id'=>$pid))->delete() ? $this->success('删除成功') : $this->error('删除失败');
			//$Node->where(array('id'=>$pid))->save(array('status'=>-2)) ? $this->success('删除成功') : $this->error('删除失败');
			exit;
		}
		
		$this->assign('pid',$pid);
		$this->assign('level',$level);
		$this->assign('PREV_URL',C('PREV_URL'));
		$this->display();
	}

	
	//节点状态管理
	public function node_status() {
		$Node = M('Node');
		$id = $this->_get('id');	//id
		$status = $this->_get('status');//状态
		
		$Node->where(array('id'=>$id))->save(array('status'=>$status)) ? $this->success('修改成功') : $this->error('修改失败');
	}
	
	
	
	/**
	 * 组与节点关系
	 */
	public function group_node () {

		//初始化数据
		$group_id = $this->_get('group_id');	//当前组ID
		empty($group_id)  ? $group_id = 1 : $group_id ;
		
		$Group = M('Group');								//组表
		$Node = M('Node');								//节点表
		$GroupNode = M('GroupNode');			//组与节点关系表
		$Db = M();												//数据库连接对象
		
		//组列表
		$group_list = $Group->field('id,name')->where(array('status'=>0))->select();		
		
		/* 获取组已有节点数据 */
		$group_auto = $Db->field('n.id,n.pid,n.name,n.title,n.level')
		->table(C('DB_PREFIX').'group_node AS gn')
		->join(C('DB_PREFIX').'node AS n ON n.id = gn.node_id')
		->where(array('gn.group_id'=>$group_id,'n.status'=>0))->order('n.pid ASC,n.level ASC')
		->select();
			
		/* 获取节点没有分配到组的数据 */
		$node_ids = getArrayByField($group_auto,'id');		//已分配的节点ID
		if (!empty($node_ids)) $map['id'] = array('not in',$node_ids);	//当组中一个权限都没有时，容错处理
		$allot_list  = $Node->where($map)->order('pid ASC,level ASC')->select();			//还未分配的节点

		$this->assign('group_id',$group_id);
		$this->assign('group_list',$group_list);
		$this->assign('group_auto',$group_auto);
		$this->assign('allot_list',$allot_list);
		
		$this->display();
	}
	
	/**
	 * 组与节点关系编辑（AJAX）
	 */
	public function edit_group_node () {
		/* 初始化数据 */
		if ($this->isPost()) {
			$data = $_POST['data'];						//请求的授权权限
			$group_id = $_POST['group_id'];		//请求的组id
			$GroupNode = M('GroupNode');			//组与节点关系表
			
			if (empty($data)) parent::callback(C('STATUS_OTHER'),'请求的节点为空');
			if (empty($group_id)) parent::callback(C('STATUS_OTHER'),'请求组为空');

			/* 请求的节点数据处理 */
			$data = stripslashes($data);					//还原转义后的字符

			$data_tmp = json_decode($data);		//转化为数组格式
	
			$auto_node = array();		//保存请求的节点ID
			foreach ($data_tmp As $key =>$val) {
				if (!empty($val->id)) {
					array_push($auto_node,$val->id);
				}
			}
			
			/* 已有的节点数据处理 */
			$node_list = $GroupNode->where(array('group_id'=>$group_id))->select();		//当前组下所有的节点数据
			$have_node = getArrayByField($node_list,'node_id');		//获取所有节点ID
			
			/* 计算需要插入与删除的节点ID */
			$action = arrar_insert_delete($auto_node,$have_node);
			
			//插入节点
			$insert = $action['insert'];
			if ($insert) {
				foreach ($insert AS $key=>$val) {
					$GroupNode->add(array('group_id'=>$group_id,'node_id'=>$val));
				}
			}
			
			//删除节点
			$delete = $action['delete'];
			if ($delete) {
				foreach ($delete AS $key=>$val) {
					$GroupNode->where(array('group_id'=>$group_id,'node_id'=>$val))->delete();
				}
			}
			
			parent::callback(C('STATUS_SUCCESS'),'更新成功');
		} else {
			parent::callback(C('STATUS_OTHER'),'非法操作！');
		}

	}
	
	
	//第二种，checkbox格式
	public function group_node_two () {
		//初始化数据
		$group_id = $this->_get('group_id');				//组id
		$module_pid = $this->_get('module_pid');		//模块pid
		$action_pid = $this->_get('action_pid');			//动作pid
		
		$Group = D('Group');								//组表
		$Node = D('Node');								//节点表
		$GroupNode = D('GroupNode');			//组与节点关系表

		/* 获取当前组名 */
		$group_name = $Group->get_one_data(array('id'=>$group_id),'name,title');
		
		/* 节点列表-按等级划分 */
		$node_group = $Node->get_spe_data(array('status'=>0,'level'=>1)); 												//一级节点(分组节点)
		$node_module = $Node->get_spe_data(array('status'=>0,'level'=>2,'pid'=>$module_pid)); 			//二级节点	(模块节点)
		$node_action = $Node->get_spe_data(array('status'=>0,'level'=>3,'pid'=>$action_pid)); 				//三级节点	(方法节点)
		
		/* 当前组下已有的节点数据 */
		$node_list = $GroupNode->get_spe_data(array('group_id'=>$group_id));		//当前组下所有的节点数据
		$have_node = getArrayByField($node_list,'node_id');		//获取所有节点ID

		/* 给当前组下已有的节点加上首选 */
		foreach ($node_group as $key=>$val) {
			if (in_array($val['id'],$have_node)) {
				$node_group[$key]['checked'] ='checked="checked"';
			}
		}
		foreach ($node_module as $key=>$val) {
			if (in_array($val['id'],$have_node)) {
				$node_module[$key]['checked'] ='checked="checked"';
			}
		}
		foreach ($node_action as $key=>$val) {
			if (in_array($val['id'],$have_node)) {
				$node_action[$key]['checked'] ='checked="checked"';
			}
		}
		
		/* 模板变量 */
		$this->assign('group_id',$group_id);
		$this->assign('module_pid',$module_pid);
		$this->assign('group_name',$group_name);
		
		$this->assign('node_group',$node_group);
		$this->assign('node_module',$node_module);
		$this->assign('node_action',$node_action);
		
		$this->assign('ACTION_NAME','分配权限');
		$this->display();
	}
	
	/**
	 * 组与节点关系编辑-第二种编辑
	 */
	public function group_node_update () {
		$group_id = $this->_post('group_id');				//组ID
		$auto_node = $this->_post('node');					//请求的节点
		$have_node = $this->_post('have_node');			//已有的节点数据
			
		//$have_node = $_POST['have_node'];

		$GroupNode = D('GroupNode');				//组与节点关系表
	
		/* 计算需要插入与删除的节点ID */
		$action = arrar_insert_delete($auto_node,$have_node);

		//插入节点
		$insert = $action['insert'];
		if ($insert) {
			foreach ($insert AS $key=>$val) {
				$GroupNode->add(array('group_id'=>$group_id,'node_id'=>$val));
			}
		}
			
		//删除节点
		$delete = $action['delete'];
		if ($delete) {
			foreach ($delete AS $key=>$val) {
				$GroupNode->where(array('group_id'=>$group_id,'node_id'=>$val))->delete();
			}
		}
		
		$this->success('已更新');
	}
	
	
	
	
	/**
	 * 组管理
	 */
	public function group() {
		
		$Group = D('Group');	//组表
		$pid = $this->_get('pid');
		empty($pid) ? $pid = 0 : $pid;
		
		//组列表
		$group_list = $Group->seek_all_data($pid);
		
		$this->assign('pid',$pid);
		$this->assign('group_list',$group_list);
		$this->display();
	}	
	
	/**
	 * 组编辑
	 */
	public function groupEdit() {
		$type = $this->_get('type');	//类型
		$id = $this->_get('id');		//操作id
		$pid = $this->_get('pid');		//操作父id
		$time = time();
		$Group = M('Group');				//组表
		
		if ($type == 'delete') {				//删除
			$Group->where(array('id'=>$id))->save(array('status'=>-2)) ? $this->success('删除成功') : $this->error('删除失败');
			exit;			
		} else if ($type == 'add') {		//添加
			if (isPost($_POST)) {
				$Group->create();
				$Group->pid = $pid;
				$Group->create_time = $time;
				$Group->update_time = $time;
				$Group->add() ? $this->success('添加成功',$_POST['PREV_URL']) : $this->error('添加失败');
				exit;
			}
			$this->assign('setName','新建组');
		} else if ($type == 'update') {			//修改
			$content  = $Group->where(array('id'=>$id))->find();	//当前组内容信息
			if (isPost($_POST)) {
				$Group->create();
				$Group->save() ? $this->success('修改成功',$_POST['PREV_URL']) : $this->error('没有做出修改');
				exit;
			}
			$this->assign('setName','修改组');
			$this->assign('id',$content['id']);
			$this->assign('group_info',$content);

		}
	
		$this->assign('pid',$pid);
		$this->assign('PREV_URL',C('PREV_URL'));
		$this->display();
	}
	
	
	
	/**
	 * 分配组用户管理
	 */
	public function group_user() {
		$Role = D('Group');						//组表
		$Users = D('Users');						//用户表
		$Db = M();										//数据库连接对象
		$id = $this->_get('id');					//组id
		
		//当前组名
		$group_name = $Role->where(array('id'=>$id,'status'=>0))->getField('name');
		if (empty($group_name)) $this->error('当前组不存在');

		//所有用户列表
		$userList = $Users->field('u.id,u.account,u.nickname')
		->table(C('DB_PREFIX').'users AS u')
		->where(array('u.is_del'=>0,'u.type'=>0))
		->select();
		
		//已有用户
		$userYesList = $Db->field('u.id,u.account,g.user_id,u.nickname')
		->table(C('DB_PREFIX').'group_user AS g')
		->join(C('DB_PREFIX')."users AS u ON g.user_id=u.id")
		->where(array('g.group_id'=>$id,'u.is_del'=>0,'u.type'=>0))
		->select();

		//计算当前组下已有的用户id
		$userid_a = array();
		foreach($userYesList as $key=>$val) {
			$userid_a[] =  $val['user_id'];
		}
		//计算当前组下没有的用户信息
		$htmlUser = array();
		foreach ($userList as $key =>$val) {
			if (!in_array($val['id'],$userid_a)) {
				$htmlUser[] = $val;
			}
		}

		$this->assign('group_name',$group_name);
		$this->assign('userYesList',$userYesList);
		$this->assign('htmlUser',$htmlUser);
		$this->display();
	}
	
	/**
	 * 用户与组管理编辑（AJAX）
	 */
	public function group_user_edit () {
		/* 初始化数据 */
		if ($this->isPost()) {
			$data = $_POST['data'];						//请求的授权权限
			$group_id = $_POST['group_id'];		//请求的组id
			$GroupUser = M('GroupUser');			//组与节点关系表
				
			if (empty($data)) parent::callback(C('STATUS_OTHER'),'请求的用户为空');
			if (empty($group_id)) parent::callback(C('STATUS_OTHER'),'请求组为空');
		
			/* 请求的节点数据处理 */
			$data_tmp = json_decode(stripslashes($data));		//转化为数组格式
			
			$auto_user = array();		//保存请求的节点ID
			foreach ($data_tmp As $key =>$val) {
				if (!empty($val->id)) {
					array_push($auto_user,$val->id);
				}
			}
				
			/* 已有的节点数据处理 */
			$user_list = $GroupUser->where(array('group_id'=>$group_id))->select();		//当前组下所有的节点数据
			$have_user = getArrayByField($user_list,'user_id');		//获取所有节点ID
				
			/* 计算需要插入与删除的节点ID */
			$action = arrar_insert_delete($auto_user,$have_user);
				
			//插入节点
			$insert = $action['insert'];
			if ($insert) {
				foreach ($insert AS $key=>$val) {
					$GroupUser->add(array('group_id'=>$group_id,'user_id'=>$val));
				}
			}
				
			//删除节点
			$delete = $action['delete'];
			if ($delete) {
				foreach ($delete AS $key=>$val) {
					$GroupUser->where(array('group_id'=>$group_id,'user_id'=>$val))->delete();
				}
			}
				
			parent::callback(C('STATUS_SUCCESS'),'更新成功');
		} else {
			parent::callback(C('STATUS_OTHER'),'非法操作！');
		}
	}
	
	

}