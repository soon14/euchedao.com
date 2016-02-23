<?php
require_once(dirname(__FILE__)."/../include/common.inc.php");
require_once SLINEINC."/view.class.php";

//顶部用登陆状态
if($dopost=='LoginStatus')
{
   if($User->IsLogin())
		{
			
			 $userinfo=$User->getInfoByMid($User->uid);
			 $uname = cn_substr($userinfo['nickname'],10);//昵称
			 $jifen=!empty($userinfo['jifen']) ? $userinfo['jifen'] : 0;
             $out ='  <a href="/"<span class="name">您好,'.$uname.'</span>';
             $out.='  <a href="'. $GLOBALS['cfg_cmsurl'].'/member/">您好,'.$uname.'</a>';		
		}
		else //未登陆状态
		{
			$out = '<a href="' . $GLOBALS['cfg_cmsurl'] . '/member/login.php">登录</a><s>/</s><a href="' . $GLOBALS['cfg_cmsurl'] . '/member/reg.php">注册</a>';
		}
	
	echo $out;
	exit();

}

//检测是否登陆
if($dopost=='checkLogin')
{
	$flag=0;
	
	if($User->isLogin())
	{
		
	   $flag=1;	
	}
	echo $flag;
	exit;
	
}
if($dopost=='getUser')
{
	if($User->isLogin())
	{
	    $cuser['name']=$User->username;
		$cuser['id']=$User->uid;
		$cuser['status']=1;
	}
	else
	  $cuser['status']=0;
	echo json_encode($cuser);
	exit;  
}

//ajaxLogin
if($dopost=='ajaxLogin')
{
	$User=new Member(7*3600);
	$flag = $User->login($uname, $pwd);
	$arr=array();
	$arr['status']=$flag;
	echo  json_encode($arr);
	exit();
	
}
//ajaxReg
if($dopost=='ajaxReg')
{

	$pwd=md5($mobile);
	$jointime=time();
	$joinip=GetIP();
	$data=array();
	$jifen=empty($cfg_reg_jifen) ? 0 : $cfg_reg_jifen;//网上注册赠送积分 
	if(!_checkMobile($mobile)) //检测此手机号是否注册
	{
        $nickname=substr($mobile,0,5).'***';
		$sql="insert into #@__member(nickname,pwd,email,mobile,jointime,joinip,jifen) values('$nickname','$pwd','','$mobile','$jointime','$joinip','$jifen')";
		
	
		
		if($dsql->ExecuteNoneQuery($sql))
		{
			$content="尊敬的用户{$mobile}你好,你已经成功注册成为{$GLOBALS['cfg_webname']}会员,你的登陆名是:{$mobile},密码是:{$mobile},为了你的帐户安全,请尽快修改密码!";

            $msgInfo = Helper_Archive::getDefineMsgInfo(0);

            if(isset($msgInfo['isopen']))
            {
                $nickname = $mobile;
                $password = $mobile;
                $content = $msgInfo['msg'];
                $content = str_replace('{#LOGINNAME#}',$mobile,$content);
                $content = str_replace('{#PASSWORD#}',$mobile,$content);
                $content = str_replace('{#WEBNAME#}',$GLOBALS['cfg_webname'],$content);
                $content = str_replace('{#PHONE#}',$GLOBALS['cfg_phone'],$content);

                Helper_Archive::sendMsg($mobile,$mobile,$content);//注册短信
            }
			//sendMsg('',$content,$mobile,'','shortmsg');
			
			$User=new Member(7*3600);
			$status = $User->login($mobile, $mobile);
			
			$data['status']=1;
			
		}
	}
	else //已经注册
	{
	  
	  $data['status']='hasReg';	
	}
	echo json_encode($data);
	exit();
}

//弹出登陆框

if($dopost == 'getloginbox')
{
	$pv = new View(0);

    $pv->SetTemplet(SLINETEMPLATE ."/".$cfg_df_style ."/" ."member/" ."pop_login.htm");

    $pv->Display();
	
}
//功能函数
//检测手机号码
function _checkMobile($mobile)
{
  global $dsql;
  $flag=0;
  $sql="select count(*) as num from #@__member where mobile='$mobile'";
 
  $row=$dsql->GetOne($sql);
 
  if($row['num']==1)
  {
	$flag=1; 
  }
 return $flag;
}

 ?>