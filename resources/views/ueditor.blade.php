<!-- 配置文件 -->
<script type="text/javascript" src="{{asset('ueditor-1.4.33/ueditor.config.js')}}"></script>
<!-- 编辑器源码文件 -->
<script type="text/javascript" src="{{asset('ueditor-1.4.33/ueditor.all.js')}}"></script>
<!-- 实例化编辑器 -->
<script type="text/javascript">
    var ue = UE.getEditor('container', {
      'serverUrl': '{{url('/admin/dash/ueditor')}}'
    });
    ue.ready(function(){
    	ue.execCommand('serverparam', '_token', '{{csrf_token()}}');
    });
</script>