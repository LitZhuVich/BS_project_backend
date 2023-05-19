{{-- 发送邮箱的样式 --}}
<h2>欢迎使用微工单系统服务</h2>
<p>用户:{{$user->username}}</p>

<span>您的邮箱验证码：<h2>{{$user->email_verification_token}}</h2></span>
