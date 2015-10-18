//配置私有云接口地址，也就是指向yangcong_private/api目录的域名地址

//需要初始化的数据
var seckenPrivate = {
    api_url:'/api',
    jumpToLogin:function(status){
        if(status == 10000){
            location.href="/login.html";
        }
    },
    checkInstall:function(){
        $.ajax({
            type:'POST',
            dataType:'jsonp',
            url:seckenPrivate.api_url + '/install/checkinstall',
            data:{},
            jsonp:'secken_jsonp_callback',
            success:function(response){

                if(response.status == 0){
                    location.href='/pages/install/index.html';
                }
            }
        });
    },
    pagination:function(page_count, current_page, bind_event){

        var ul = '';
            ul += '<ul class="pagination pagination-sm no-margin pull-right">';
            ul += '<li><a href="#">«</a></li>';
            for(var i=1; i<=page_count; i++){
                var active = '';
                if(i == current_page){
                    active = 'class = "active"';
                }
                ul += '<li '+active+'><a href="#" onclick="'+bind_event+'('+i+')">'+i+'</a></li>';
            }
            ul += '<li><a href="#">»</a></li>';
            ul += '</ul>';

        $('#pagination').html(ul);
    },
    //安装
    install: {
        checkItem:function(){
            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/install/check',
                data:{},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#env').html('加载中...');
                },
                success:function(response){
                    $('#env').html('');

                    if(response.status == 1){
                        //加载环境
                        $.each(response.data.env,function(i, n){
                            var tr = '<tr>';
                                tr += '<td width="25%">'+i+'</td>';
                                tr += '<td width="20%">'+n.need+'</td>';
                                tr += '<td width="15%">'+n.best+'</td>';
                                tr += '<td width="40%">'+n.current+'</td>';
                                tr += '</tr>';

                            $('#env').append(tr);
                        });
                        //加载目录权限
                        $.each(response.data.writeable,function(i, n){

                            var status = n ? '可写' : '<span style="color:red">不可写</span>';

                            var tr = '<tr>';
                                tr += '<td width="25%">'+i+'</td>';
                                tr += '<td width="25%"></td>';
                                tr += '<td width="25%">可写</td>';
                                tr += '<td width="25%">'+status+'</td>';
                                tr += '</tr>';

                            $('#writeable').append(tr);
                        });
                    }else{
                        $('#group').html('<red>加载失败</red>');
                    }

                    if(response.data.allow_next == 1){
                        $('#next').html('<button type="button" class="btn btn-info pull-right" onclick="javascript:location.href=\'/pages/install/db.html\'">下一步</button>');
                    }
                }
            })
        },
        dbConfig:function(){
            var host = $('#host').val();
            var dbname = $('#dbname').val();
            var dbuser = $('#dbuser').val();
            var dbpwd = $('#dbpwd').val();
            var dbpre = $('#dbpre').val();

            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/install/addconfig',
                data:{host_name:host,db_name:dbname,db_user:dbuser,db_pwd:dbpwd,dbpre:dbpre},
                jsonp:'secken_jsonp_callback',
                success:function(response){

                    if(response.status == 1){
                        location.href='/pages/install/execsql.html';
                    }else{
                        $('#tip').html('<red>设置失败</red>');
                    }
                }
            });
        },
        dbInstall:function(){
            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/install/addtable',
                data:{},
                jsonp:'secken_jsonp_callback',
                success:function(response){
                    if(response.status == 1){
                        location.href='/pages/activate/index.html';
                    }
                }
            });
        }
    },
    activate: {
        addAppInfo:function(){
            var app_id = $('input[name=app_id]').val();
            var app_key = $('input[name=app_key]').val();

            if(app_id.length == 0){
                $('#tip').addClass('callout callout-danger').text('应用ID不能为空').show();
                $('input[name=app_id]').focus();
                return;
            }

            if(app_key.length == 0){
                $('#tip').addClass('callout callout-danger').text('应用KEY不能为空').show();
                $('input[name=app_key]').focus();
                return;
            }

            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/setting/set',
                data:{app_id:app_id,app_key:app_key},
                jsonp:'secken_jsonp_callback',
                success:function(response){
                    if(response.status == 1){
                        location.href='/pages/activate/auth.html';
                    }
                }
            });
        },
        qrcodeForAuth:function(){
            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/setting/getauthqrcode',
                data:{},
                jsonp:'secken_jsonp_callback',
                success:function(response){
                    if(response.status == 1){
                        event_id = response.data.event_id;

                        var qrcode = '<div style="width:300px;height:400;margin:0 auto;">';
                            qrcode += '<img src="'+response.data.qrcode+'" width="300" height="300" />';
                            qrcode += '<center><b id="result_msg">'+response.description+'</b></center>';
                            qrcode += '</div>';

                        $('.box-body').append(qrcode);
                    }
                }
            });
        },
        getResult:function(event_id){
            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/setting/getresult',
                data:{event_id:event_id},
                jsonp:'secken_jsonp_callback',
                success:function(response){
                    if(response.status == 1){
                        location.href="/pages/activate/bindcreator.html";
                    }else{
                        $('#result_msg').html(response.description);
                    }
                }
            });
        }
    },
    //分组
    group: {
        getList:function(){
            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/group',
                data:{},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#group').html('加载中...');
                },
                success:function(response){
                    $('#group').html('');

                    seckenPrivate.jumpToLogin(response.status);

                    if(response.status == 1){
                        $.each(response.data,function(i, n){
                            seckenPrivate.user.getGid();

                            var active = n.gid == seckenPrivate.user.gid ? 'class="active"' : '';

                            var li = '<li '+active+'>';
                                li += '<a class="inline-link" href="/index.html?g='+n.gid+'">';
                                li += n.gname;
                                li += '</a>';
                                if(n.inner == 0){
                                    li += '<i class="fa fa-fw fa-pencil-square-o" data-gname="'+n.gname+'" data-gid="'+n.gid+'" onclick="javascript:seckenPrivate.group.editModal(this);"></i>';
                                    li += '<i class="fa fa-fw fa-times-circle-o" data-gid="'+n.gid+'" onclick="javascript:seckenPrivate.group.delModal(this);"></i>';
                                }
                                li +='</li>';
                            $('#group').append(li);
                        });
                    }else{
                        $('#group').html('加载失败');
                    }
                }
            });
        },
        editModal:function(n){
            $('input[name=e_gid]').val($(n).data('gid'));
            $('input[name=edit_group_name]').val($(n).data('gname'));

            $('#editGroup').modal();
        },
        delModal:function(n){
            $('input[name=d_gid]').val($(n).data('gid'));
            $('#delGroup').modal();
        },
        add:function(){
            var group_name = $("input[name=group_name]").val();
            if(group_name.length == 0){
                $('#g_tip').addClass('callout callout-danger').text('用户组信息不能为空').show();
                $('input[name=group_name]').focus();
                return;
            }

            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/group/add',
                data:{group_name:group_name},
                jsonp:'secken_jsonp_callback',
                success:function(response){

                    seckenPrivate.jumpToLogin(response.status);

                    if(response.status == 1){
                        $('#g_tip').addClass('callout callout-success').text('添加成功').show();
                        history.go(0);
                    }else{
                        $('#g_tip').addClass('callout callout-danger').text(response.description).show();
                    }
                }
            });
        },
        edit:function(){
            var group_name = $("input[name=edit_group_name]").val();
            var gid = $('input[name=e_gid]').val();

            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/group/edit',
                data:{group_name:group_name, gid:gid},
                jsonp:'secken_jsonp_callback',
                success:function(response){

                    seckenPrivate.jumpToLogin(response.status);

                    if(response.status == 1){
                        $('#eg_tip').addClass('callout callout-success').text('修改成功').show();
                        history.go(0);
                    }else{
                        $('#eg_tip').addClass('callout callout-danger').text(response.description).show();
                    }
                }
            });

        },
        delete:function(){
            var gid = $('input[name=d_gid]').val();

            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/group/delete',
                data:{gid:gid},
                jsonp:'secken_jsonp_callback',
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        $('#g_tip').addClass('callout callout-success').text('删除成功').show();
                        location.href="/index.html";
                    }else{
                        $('#g_tip').addClass('callout callout-danger').text(response.description).show();
                    }
                }
            });
        },
        setPower:function(i, s){

            var power_id = $(i).val();
            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/group/setpower',
                data:{gid:seckenPrivate.user.gid, power_id:power_id, set:s},
                jsonp:'secken_jsonp_callback',
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        $(i).parent().remove();

                        if(s == 0){
                            var li = '<li style="width:100px; list-style-type:none;float:left;">';
                                li += '<label>';
                                li += '<input type="checkbox" name="no_use_power" value="'+power_id+'" title="'+$(i).attr('title')+'"  onclick="seckenPrivate.group.setPower(this, 1)">';
                                li += $(i).attr("title");
                                li += '</label>';
                                li += '</li>';

                                $('#pnouse').append(li);
                            }else{
                                var li = '<li style="width:100px; list-style-type:none;float:left;">';
                                    li += '<label>';
                                    li += '<input type="checkbox" name="has_used_power" value="'+power_id+'" title="'+$(i).attr('title')+'"  onclick="seckenPrivate.group.setPower(this, 0)">';
                                    li += $(i).attr('title');
                                    li += '</label>';
                                    li += '</li>';

                                $('#pused').append(li);
                            }
                        }
                    }
            });
        },
        power:function(){

            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/group/getpower',
                data:{gid:seckenPrivate.user.gid},
                jsonp:'secken_jsonp_callback',
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        var checked_powers = [];
                        $.each(response.data,function(i, n){
                            checked_powers[i] = n.id;

                            var li = '<li style="width:100px; list-style-type:none;float:left;">';
                                li += '<label>';
                                li += '<input type="checkbox" name="has_used_power" value="'+n.id+'"  title="'+n.power_name+'" onclick="seckenPrivate.group.setPower(this, 0)">';
                                li += n.power_name;
                                li += '</label>';
                                li += '</li>';

                            $('#pused').append(li);
                        });

                        $.ajax({
                            type:'POST',
                            dataType:'jsonp',
                            url:seckenPrivate.api_url + '/web/power',
                            data:{},
                            jsonp:'secken_jsonp_callback',
                            success:function(response){

                                if(response.status == 1){
                                    $.each(response.data,function(i, n){

                                        if($.inArray(n.id, checked_powers) == -1){
                                            var li = '<li style="width:100px; list-style-type:none;float:left;">';
                                                li += '<label>';
                                                li += '<input type="checkbox" name="no_use_power" value="'+n.id+'" title="'+n.name+'" onclick="seckenPrivate.group.setPower(this, 1);">';
                                                li += n.name;
                                                li += '</label>';
                                                li += '</li>';

                                            $('#pnouse').append(li);
                                        }
                                    });
                                }
                            }
                        });


                    }
                }
            });
        }
    },
    //用户
    user: {
        gid : 1,
        login:function(event_id){
            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/setting/getresult',
                data:{event_id:event_id},
                jsonp:'secken_jsonp_callback',
                success:function(response){
                    if(response.status == 1){
                        $.ajax({
                            type:'POST',
                            dataType:'jsonp',
                            url:seckenPrivate.api_url + '/web/user/savesess',
                            data:{service_type:response.data.service_type, identity_name:response.data.identity_name},
                            jsonp:'secken_jsonp_callback',
                            success:function(response){
                                if(response.status == 1){
                                    $.cookie('user_id', response.data.user_id);
                                    $.cookie('user_name', response.data.user_name);
                                    $.cookie('true_name', response.data.true_name);
                                    $.cookie('company_logo', response.data.company_logo);

                                    location.href="/index.html";
                                }else{
                                    $('.login-box-msg').html(response.description);
                                }
                            }
                        });
                    }else{
                        $('#result_msg').html(response.description);
                    }
                }
            });
        },
        logout:function(){
            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/user/destroysess',
                data:{},
                jsonp:'secken_jsonp_callback',
                success:function(response){
                    if(response.status == 1){
                        $.cookie('user_id','',{expires: -1});
                        $.cookie('user_name','',{expires:-1});
                        $.cookie('true_name','',{expires: -1});
                        $.cookie('company_logo','',{expires: -1});

                        location.href="/login.html";
                    }
                }
            });
        },
        getGid:function() {
            var reg = new RegExp("(^|&)g=([^&]*)(&|$)", "i");
            var r = window.location.search.substr(1).match(reg);

            if(r != null){
                this.gid = unescape(r[2]);
            }
        },
        bind:function(){
            var add_admin  = this.add(0);
            if(add_admin != 0){
                $.ajax({
                    type:'POST',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/install/touchinstallfile',
                    data:{},
                    jsonp:'secken_jsonp_callback',
                    success:function(response){
                        if(response.status == 1){
                            location.href="/login.html";
                        }
                    }
                });
            }
        },
        getList:function(page){

            var wd = $('input[name=wd]').val();

            if(wd.length == 0){
                $.ajax({
                    type:'post',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/web/user',
                    data:{gid:this.gid,page:page},
                    jsonp:'secken_jsonp_callback',
                    beforeSend:function(){
                        $('#user > tbody').html('<center>正在拉取列表数据...</center>');
                    },
                    success:function(response){
                        $('#user > tbody').html('');

                        if(response.status == 1){
                            seckenPrivate.jumpToLogin(response.status);
                            $.each(response.data,function(i, n){
                                var tr = '<tr>';
                                var disable = '';
                                if(n.user_id == $.cookie('user_id')){
                                    var disable = 'disabled=disabled';
                                }
                                tr += '<td><label><input type="checkbox" name="u" value="'+n.user_id+'" '+disable+'/></label></td>';
                                tr += '<td>'+n.user_name+'</td>';
                                tr += '<td>'+n.true_name+'</td>';
                                tr += '<td>'+n.phone+'</td>';
                                tr += '<td>'+n.status+'</td>';
                                tr += '<td>'+n.update_time+'</td>';
                                tr += '<td><a href="#" data-userid = "'+n.user_id+'" data-truename = "'+n.true_name+'" data-open="'+n.status+'" data-createtime= "'+n.create_time+'" data-updatetime="'+n.update_time+'"data-toggle="modal" onclick="javascript:seckenPrivate.user.transmit(this);">修改</a></td>';
                                tr += '</tr>';

                                $('#user > tbody').append(tr);
                            });

                            seckenPrivate.pagination(response.count, page, 'seckenPrivate.user.getList');
                        }else{
                            $('#group').html('<red>列表拉取失败</red>');
                        }
                    }
                });
            }else{
                seckenPrivate.user.search(page);
            }
        },
        transmit:function(n){
            $('input[name=hidden_userid]').val($(n).data('userid'));
            $('input[name=edit_true_name]').val($(n).data('truename'));
            $('input[name=edit_create_time]').val($(n).data('createtime'));
            $('input[name=edit_update_time]').val($(n).data('updatetime'));
            if($(n).data('open') == '启用'){
                $('input:radio[name=edit_status][value=1]').attr('checked', 'checked');
            }else{
                $('input:radio[name=edit_status][value=0]').attr('checked', 'checked');
            }

            $('#editUser').modal();

        },
        add:function(jump){
            var gid = seckenPrivate.user.gid;
            var user_name = $('input[name=user_name]').val();
            var phone = $('input[name=phone]').val();
            var true_name = $('input[name=true_name]').val();
            var is_admin_obj = $('input[name=is_admin]');
            var is_admin = 0;

            if(is_admin_obj.val() != undefined){
                is_admin = 1;
            }

            if(user_name.length == 0){
                $('#tip').addClass('callout callout-danger').text('用户名不能为空').show();
                $('input[name=user_name]').focus();
                return 0;
            }
            if(phone.length == 0){
                $('#tip').addClass('callout callout-danger').text('手机号不能为空').show();
                $('input[name=phone]').focus();
                return 0;
            }
            if(true_name.length == 0){
                $('#tip').addClass('callout callout-danger').text('姓名不能为空').show();
                $('input[name=true_name]').focus();
                return 0;
            }

            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/user/add',
                data:{gid:gid,user_name:user_name,phone:phone,true_name:true_name,is_admin:is_admin},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('button[name=adduser]').attr('disabled','disabled');
                },
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){

                        $('#tip').addClass('callout callout-success').text('添加成功').show();
                        if(jump){
                            history.go(0);
                        }
                    }else{
                        $('#tip').addClass('callout callout-danger').text(response.description).show();
                        $('button[name=adduser]').removeAttr('disabled');
                    }
                }
            });
        },
        edit:function(){
            var user_id = $('input[name=hidden_userid]').val();
            var open = $('input:radio[name=edit_status]:checked').val();
            var true_name = $('input[name=edit_true_name]').val();

            if(true_name.length == 0){
                $('#e_tip').addClass('callout callout-danger').text('姓名不能为空').show();
                $('input[name=edit_true_name]').focus();
                return;
            }

            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/user/edit',
                data:{uid:user_id,true_name:true_name,open:open},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('button[name=edituser]').attr('disabled','disabled');
                },
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){

                        $('#e_tip').addClass('callout callout-success').text('修改成功').show();
                        history.go(0);
                    }else{
                        $('#e_tip').addClass('callout callout-danger').text(response.description).show();
                        $('button[name=edituser]').removeAttr('disabled');
                    }
                }
            });
        },
        delete:function(){
            var uids = '';
            $("input:checkbox[name=u]:checked").each(function(){
                uids += $(this).val() + '-';
            });

            if(uids.length == 0){
                alert('请选择需要删除的用户');
                return false;
            }
            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/user/delete',
                data:{uids:uids},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('button[name=delbtn]').attr('disabled','disabled');
                },
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        history.go(0);
                    }else{
                        alert(response.description);
                        $('button[name=delbtn]').removeAttr('disabled');
                    }
                }
            });
        },
        move:function(){

            var uids = '';
            $("input:checkbox[name=u]:checked").each(function(){
                uids += $(this).val() + '-';
            });

            var gid = '';
            $("input:radio[name=moveGroup]:checked").each(function(){
                gid = $(this).val();
            });

            if(gid.length == 0){
                alert('请选择分组信息');
                return false;
            }

            if(uids.length == 0){
                alert('请选择需要移动的用户');
                return false;
            }

            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/user/move',
                data:{gid:gid,uids:uids},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('button[name=movebtn]').attr('disabled','disabled');
                },
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        history.go(0);
                    }else{
                        alert(response.description);
                        $('button[name=movebtn]').removeAttr('disabled');
                    }
                }
            });
        },
        import:function(){
            var timestamp = Math.round(new Date().getTime()/1000);

            $('#file_upload').uploadify({
                'formData' : {
                    'timestamp' : timestamp,
                    'token'     : Math.floor(Math.random()*100000)
                },
                'swf'      : '../../plugins/uploadify/uploadify.swf',
                'uploader' : seckenPrivate.api_url +'/web/user/import',
                'buttonText': '批量导入',
                'fileTypeExts': '*.xls;*.xlsx',
                'sizeLimit': 2048,
                'simUploadLimit':1,
                'fileObjName':'userfile',
                'onUploadSuccess':function(file, data, response){
                    var file_data = eval(data);
                    var success_row = file_data.data.success_row;
                    var error_row = file_data.data.error_row;
                    var error = file_data.data.error;

                    if(error_row > 0){

                        var table = '<table class="table table-bordered table-striped">';
                            table += '<thead>';
                            table += '<tr>';
                            table += '<td width="11%">行号</td>';
                            table += '<td width="14%">用户名</td>';
                            table += '<td width="15%">姓名</td>';
                            table += '<td width="15%">手机号</td>';
                            table += '<td width="45%">错误</td>';
                            table += '</tr>';
                            table += '</thead>';
                            table += '<tbody>';

                            $.each(error, function(i, n){
                                table += '<tr>';
                                table += '<td width="10%">' + n.row + '</td>';
                                table += '<td width="15%">' + n.user_name + '</td>';
                                table += '<td width="15%">' + n.true_name + '</td>';
                                table += '<td width="15%">' + n.phone + '</td>';
                                table += '<td width="45%">' + n.error + '</td>';
                                table += '</tr>';
                            });
                            table += '</tbody>';
                            table += '</table>';

                            $('#import_result_tip').html(table);
                    }
                    $('#import_result').modal();
                    //$('.profile-user-img').attr('src',seckenPrivate.api_url+ '/resources/'+file_data.data.logo);
                }
            });
        },
        search:function(page){
            var wd = $('input[name=wd]').val();
            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/user/search',
                data:{wd:wd,gid:this.gid, page:page},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#user > tbody').html('<center>正在拉取列表数据...</center>');
                },
                success:function(response){
                    $('#user > tbody').html('');
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        $.each(response.data,function(i, n){
                            var tr = '<tr>';
                                tr += '<td><label><input type="checkbox" name="u" value="'+n.user_id+'"/></label></td>';
                                tr += '<td>'+n.user_name+'</td>';
                                tr += '<td>'+n.true_name+'</td>';
                                tr += '<td>'+n.phone+'</td>';
                                tr += '<td>'+n.status+'</td>';
                                tr += '<td>'+n.update_time+'</td>';
                                tr += '<td><a href="#" data-userid = "'+n.user_id+'" data-truename = "'+n.true_name+'" data-open="'+n.status+'" data-createtime= "'+n.create_time+'" data-updatetime="'+n.update_time+'"data-toggle="modal" onclick="javascript:seckenPrivate.user.transmit(this);">修改</a></td>';
                                tr += '</tr>';
                            $('#user > tbody').append(tr);
                        });

                        seckenPrivate.pagination(response.count, page, 'seckenPrivate.user.getList');

                    }else{
                        $('#group').html('<red>列表拉取失败</red>');
                    }
                }
            });
        },
        group:function(){
            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/group',
                data:{},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#groupItem').html('加载中...');
                },
                success:function(response){
                    $('#groupItem').html('');

                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        $.each(response.data,function(i, n){
                            var radio = '<label>';
                                radio += '<input type="radio" name="moveGroup" value="'+n.gid+'"/>';
                                radio += '&nbsp;&nbsp;';
                                radio += n.gname;
                                radio +='</label>';

                            $('#groupItem').append(radio);
                        });
                    }else{
                        $('#groupItem').html('<red>加载失败</red>');
                    }
                }
            });
        },
        groupPower:function(){
            $.ajax({
                type:'POST',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/group/getpower',
                data:{gid:this.gid},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#power_count').text('0');
                    $('#power_enable_count').text('0');
                },
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        var power_count = 0;
                        var power_enable_count = 0;
                        $.each(response.data,function(i, n){
                            power_count++;
                            if(n.power_status == 1){
                                power_enable_count++;
                            }
                        });
                        $('#power_count').text(power_count);
                        $('#power_enable_count').text(power_enable_count);
                    }
                }
            });
        }
    },
    //权限
    power:{
        getList:function(page){

            var wd = $('input[name=wd]').val();
            if(wd.length == 0){
                $.ajax({
                    type:'post',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/web/power',
                    data:{page:page},
                    jsonp:'secken_jsonp_callback',
                    beforeSend:function(){
                        $('#power > tbody').html('<center>正在拉取列表数据...</center>');
                    },
                    success:function(response){
                        $('#power > tbody').html('');
                        seckenPrivate.jumpToLogin(response.status);
                        if(response.status == 1){
                            $.each(response.data,function(i, n){
                                var open = n.status == 1 ? '开启' : '关闭';
                                var tr = '<tr>';
                                    tr += '<td>'+n.name+'</td>';
                                    tr += '<td>'+n.intro+'</td>';
                                    tr += '<td>'+open+'</td>';
                                    tr += '<td><a href="#" data-id="'+n.id+'" data-intro="'+n.intro+'" data-name="'+n.name+'" data-powerid="'+n.power_id+'" data-powerkey="'+n.power_key+'" data-status="'+n.status+'" onclick="javascript:seckenPrivate.power.transmit(this);">设置</a>';
                                    tr += '<a href="#" onclick="javascript:seckenPrivate.power.transmitdelbox('+n.id+')">删除</a></td>';
                                    tr += '</tr>';
                                $('#power > tbody').append(tr);
                            });

                            seckenPrivate.pagination(response.count, page, 'seckenPrivate.power.getList');
                        }else{
                            $('#power').html('<red>列表拉取失败</red>');
                        }
                    }
                });
            }else{
                seckenPrivate.power.search(page);
            }
        },
        transmit:function(n){
            $('input[name=hidden_id]').val($(n).data('id'));
            $('input[name=edit_name]').val($(n).data('name'));
            $('#edit_intro').val($(n).data('intro'));
            $('input[name=edit_powerid]').val($(n).data('powerid'));
            $('input[name=edit_powerkey]').val($(n).data('powerkey'));
            if($(n).data('status') == 1){
                $('input:radio[name=edit_status][value=1]').attr('checked', 'checked');
            }else{
                $('input:radio[name=edit_status][value=0]').attr('checked', 'checked');
            }

            $('#setPower').modal();
        },
        transmitdelbox:function(id){
            $('input[name=hidden_id]').val(id);
            $('#delPower').modal();
        },
        search:function(page){
            var wd = $('input[name=wd]').val();
            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/power/search',
                data:{power_name:wd,page:page},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#power > tbody').html('<center>正在拉取列表数据...</center>');
                },
                success:function(response){
                    $('#power > tbody').html('');
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        $.each(response.data,function(i, n){
                            var open = n.status == 1 ? '开启' : '关闭';
                            var tr = '<tr>';
                                tr += '<td>'+n.name+'</td>';
                                tr += '<td>'+n.intro+'</td>';
                                tr += '<td>'+open+'</td>';
                                tr += '<td><a href="#" data-id="'+n.id+'" data-intro="'+n.intro+'" data-name="'+n.name+'" data-powerid="'+n.power_id+'" data-powerkey="'+n.power_key+'" data-status="'+n.status+'" onclick="javascript:seckenPrivate.power.transmit(this);">设置</a>';
                                tr += '<a href="#" onclick="javascript:seckenPrivate.power.transmitdelbox('+n.id+')">删除</a></td>';
                                tr += '</tr>';
                            $('#power > tbody').append(tr);
                        });

                        seckenPrivate.pagination(response.count, page, 'seckenPrivate.power.getList');

                    }else{
                        $('#power').html('<red>列表拉取失败</red>');
                    }
                }
            });
        },
        add:function(){
            var power_name = $('input[name=power_name]').val();
            var power_intro = $('#power_intro').val();

            if(power_name.length == 0){
                $('#tip').addClass('callout callout-danger').text('权限名不能为空').show();
                $('input[name=power_name]').focus();
                return;
            }

            if(power_intro.length == 0){
                $('#tip').addClass('callout callout-danger').text('权限简介不能为空').show();
                $('#power_intro').focus();
                return;
            }

            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/power/add',
                data:{power_name:power_name,power_intro:power_intro},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('button[name=addpower]').attr('disabled','disabled');
                },
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){

                        $('#tip').addClass('callout callout-success').text('添加成功').show();
                        history.go(0);
                    }else{
                        $('#tip').addClass('callout callout-danger').text(response.description).show();
                        $('button[name=addpower]').removeAttr('disabled');
                    }
                }
            });
        },
        edit:function(){
            var power_id = $('input[name=hidden_id]').val();
            var power_name = $('input[name=edit_name]').val();
            var power_intro = $('#edit_intro').val();
            var power_status = $('input:radio[name=edit_status]:checked').val();

            if(power_name.length == 0){
                $('#e_tip').addClass('callout callout-danger').text('权限名不能为空').show();
                $('input[name=power_name]').focus();
                return;
            }

            if(power_intro.length == 0){
                $('#e_tip').addClass('callout callout-danger').text('权限简介不能为空').show();
                $('#edit_intro').focus();
                return;
            }

            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/power/edit',
                data:{power_name:power_name,power_intro:power_intro,power_status:power_status,power_id:power_id},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('button[name=setpower]').attr('disabled','disabled');
                },
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){

                        $('#e_tip').addClass('callout callout-success').text('添加成功').show();
                        history.go(0);
                    }else{
                        $('#e_tip').addClass('callout callout-danger').text(response.description).show();
                        $('button[name=setpower]').removeAttr('disabled');
                    }
                }
            });
        },
        regenKey:function(){
            var id = $('input[name=hidden_id]').val();
            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/power/regenkey',
                data:{id:id},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('button[name=regenkey]').attr('disabled','disabled');
                },
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    $('button[name=regenkey]').removeAttr('disabled');
                    if(response.status == 1){
                        $('input[name=edit_powerid]').val(response.data.power_id);
                        $('input[name=edit_powerkey]').val(response.data.power_key);
                    }
                }
            });
        },
        delete:function(){
            var power_id = $('input[name=hidden_id]').val();

            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/power/delete',
                data:{power_id:power_id},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('button[name=delpower]').attr('disabled','disabled');
                },
                success:function(response){
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        history.go(0);
                    }else{
                        alert(response.description);
                    }
                }
            });
        }
    },
    //统计
    authStats:{
        defaultTab:'',
        PieData:[],
        total:function(pieChart, pieOptions){
            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/stats/auth',
                data:{},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#pieChart').html('<center>正在渲染...</center>');
                },
                success:function(response){
                    $('#pieChart').html('');
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        var colors = ['#f56954','#00a65a','#f39c12','#00c0ef'];
                        var PieDataTmp = [];
                        $.each(response.data,function(i, n){
                            var sData = {};
                            sData.value = n.sum;
                            sData.color = colors[i];
                            sData.highlight = colors[i];
                            sData.label = n.auth_name;

                            PieDataTmp[i] = sData;
                        });

                        this.PieData = PieDataTmp;

                        pieChart.Doughnut(this.PieData, pieOptions);
                    }else{
                        $('#pieChart').html('<red>暂无数据</red>');
                    }
                }
            });
        },
        date:function(barChart, barChartData, barChartOptions){
            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/stats/date',
                data:{},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#pieChart').html('<center>正在渲染...</center>');
                },
                success:function(response){
                    $('#pieChart').html('');
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){

                        barChartData.datasets[0].data = []; // 昨日
                        barChartData.datasets[1].data = []; // 本周
                        barChartData.datasets[2].data = []; // 本月

                        $.each(response.data,function(i, n){

                            barChartData.datasets[0].data[n.auth_type - 1] = n.day_count; // 昨日
                            barChartData.datasets[1].data[n.auth_type - 1] = n.week_count; // 本周
                            barChartData.datasets[2].data[n.auth_type - 1] = n.month_count; // 本月

                        });

                        barChart.Bar(barChartData, barChartOptions);
                    }else{
                        $('#pieChart').html('<red>暂无数据</red>');
                    }
                }
            });
        },
        detail:function(page){

            var power_id = seckenPrivate.authStats.defaultTab;

            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/stats/detail',
                data:{power_id:power_id, page:page},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#auth_detai').html('<center>正在拉取列表数据...</center>');
                },
                success:function(response){
                    $('#auth_detai').html('');
                    seckenPrivate.jumpToLogin(response.status);

                    if(response.status == 1){
                        $.each(response.data,function(i, n){
                            if(i == 0){
                                seckenPrivate.authStats.defaultTab = n.id;
                            }
                            var tr = '<tr>';
                                tr += '<td>' + n.statistics_time + '</td>';
                                tr += '<td>' + n.click_sum + '</td>';
                                tr += '<td>' + n.hand_sum + '</td>';
                                tr += '<td>' + n.face_sum + '</td>';
                                tr += '<td>' + n.noice_sum + '</td>';
                                tr += '</tr>';

                            $('#auth_detai').append(tr);
                        });
                        seckenPrivate.pagination(response.count, page, 'seckenPrivate.authStats.detail');
                    }else{
                        $('#auth_detai').html('<red>列表拉取失败</red>');
                    }
                }
            });
        },
        powerTab:function(){
            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/power',
                data:{},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#power_tab').html('<center>正在拉取列表数据...</center>');
                },
                success:function(response){
                    $('#power_tab').html('');
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        $.each(response.data,function(i, n){
                            if(i == 0){
                                seckenPrivate.authStats.defaultTab = n.id;
                                seckenPrivate.authStats.detail(1);
                            }
                            var btn = '<button type="button" class="btn bg-olive margin" onclick="seckenPrivate.authStats.defaultTab = '+n.id+';seckenPrivate.authStats.detail(1);">';
                                btn += n.name;
                                btn += '</button>';
                            $('#power_tab').append(btn);
                        });
                    }else{
                        $('#power_tab').html('<red>列表拉取失败</red>');
                    }
                }
            });
        }
    },
    //日志
    log:{
        authList:function(page){
            var auth_type = $('input:radio[name=auth_type]:checked').val();
            var auth_result=$('input:radio[name=auth_result]:checked').val();
            auth_type = auth_type ? auth_type : 0;
            auth_result = auth_result ? auth_result : -1;

            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/log/auth',
                data:{auth_type:auth_type,auth_result:auth_result,page:page},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#auth_log > tbody').html('<center>正在拉取列表数据...</center>');
                },
                success:function(response){
                    $('#auth_log > tbody').html('');
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        $.each(response.data,function(i, n){
                            var tr = '<tr>';
                                tr += '<td>'+n.auth_user+'</td>';
                                tr += '<td>'+n.auth_type+'</td>';
                                tr += '<td>'+n.power_name+'</td>';
                                tr += '<td>'+n.auth_time+'</td>';
                                tr += '<td>'+n.auth_result+'</td>';
                                tr += '</tr>';
                            $('#auth_log > tbody').append(tr);
                        });
                        seckenPrivate.pagination(response.count, page, 'seckenPrivate.log.authList');
                    }else{
                        $('#auth_log').html('<red>列表拉取失败</red>');
                    }
                }
            });
        },
        opList:function(page){
            var op_result = $('input:radio[name=op_result]:checked').val();
            op_result = op_result ? op_result : -1;

            $.ajax({
                type:'post',
                dataType:'jsonp',
                url:seckenPrivate.api_url + '/web/log/op',
                data:{op_status:op_result,page:page},
                jsonp:'secken_jsonp_callback',
                beforeSend:function(){
                    $('#op_log > tbody').html('<center>正在拉取列表数据...</center>');
                },
                success:function(response){
                    $('#op_log > tbody').html('');
                    seckenPrivate.jumpToLogin(response.status);
                    if(response.status == 1){
                        $.each(response.data,function(i, n){
                            var tr = '<tr>';
                                tr += '<td>'+n.op_user+'</td>';
                                tr += '<td>'+n.op_name+'</td>';
                                tr += '<td>'+n.op_intro+'</td>';
                                tr += '<td>'+n.op_status+'</td>';
                                tr += '<td>'+n.op_time+'</td>';
                                tr += '</tr>';
                            $('#op_log > tbody').append(tr);
                        });
                        seckenPrivate.pagination(response.count, page, 'seckenPrivate.log.opList');
                    }else{
                        $('#op_log').html('<red>列表拉取失败</red>');
                    }
                }
            });
        }
    },
    //设置
    setting:{
        //企业信息设置
        company:{
            info:function(){
                $.ajax({
                    type:'post',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/web/company/info',
                    data:{},
                    jsonp:'secken_jsonp_callback',
                    success:function(response){
                        seckenPrivate.jumpToLogin(response.status);
                        if(response.status == 1){
                            if(response.data.company_logo.length != 0){
                                $('#company_photo').attr('src', seckenPrivate.api_url+'/resources/'+response.data.company_logo);
                            }
                            $('#company_name').text(response.data.company_name);
                        }
                    }
                });

                $.ajax({
                    type:'post',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/web/user/admin',
                    data:{},
                    jsonp:'secken_jsonp_callback',
                    success:function(response){
                        seckenPrivate.jumpToLogin(response.status);
                        if(response.status == 1){
                            $('#user_name').text(response.data.user_name);
                            $('#phone').text(response.data.phone);
                            $('#register_time').text(response.data.create_time);
                        }
                    }
                });

            },
            edit:function(){

                var company_name = $('input[name=company_name]').val();

                $.ajax({
                    type:'post',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/web/company/set',
                    data:{company_name:company_name},
                    jsonp:'secken_jsonp_callback',
                    success:function(response){
                        seckenPrivate.jumpToLogin(response.status);
                        if(response.status == 1){
                            history.go(0);
                        }else{
                            $('#tip').addClass('callout callout-danger').text(response.description).show();
                        }
                    }
                });
            },
            displayupload:function(){
                var timestamp = Math.round(new Date().getTime()/1000);

                $('#file_upload').uploadify({
            		'formData' : {
            			'timestamp' : timestamp,
            			'token'     : Math.floor(Math.random()*100000)
            		},
            		'swf'      : '../../plugins/uploadify/uploadify.swf',
            		'uploader' : seckenPrivate.api_url +'/web/company/upload',
                    'buttonText': '上传图片',
                    'fileTypeExts': '*.gif; *.jpg; *.png',
                    'sizeLimit': 1024,
                    'simUploadLimit':1,
                    'fileObjName':'userfile',
                    'onUploadSuccess':function(file, data, response){
                        var file_data = eval(data);
                        $('.profile-user-img').attr('src',seckenPrivate.api_url+ '/resources/'+file_data.data.logo);
                    }
            	});
            }
        },
        upgrade:{
            lastest_version:{},

            getList:function(){
                $.ajax({
                    type:'post',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/web/version/info',
                    data:{},
                    jsonp:'secken_jsonp_callback',
                    success:function(response){
                        seckenPrivate.jumpToLogin(response.status);

                        if(response.status == 1){
                            $.each(response.data, function(i, n){
                                var li = '<li class="list-group-item">';
                                    li += '<b>'+n.dependent_info+'</b>';
                                    li += '<a class="pull-right" style="cursor:pointer"  onclick="seckenPrivate.setting.upgrade.check(this,'+n.dependent_code+','+n.version_code+');">检查更新</a>';
                                    li += '</li>';

                                    $('#version_info').append(li);
                            });

                        }else{
                            $('#tip').addClass('callout callout-danger').text('更新失败').show();
                        }
                    }
                });
            },
            check:function(obj, dependent_code, version_code){
                $.ajax({
                    type:'post',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/web/upgrade/check',
                    data:{dependent_code:dependent_code, version_code:version_code},
                    jsonp:'secken_jsonp_callback',
                    beforeSend:function(){
                        $(obj).html('<i class="fa fa-refresh fa-spin"></i>');
                    },
                    success:function(response){
                        seckenPrivate.jumpToLogin(response.status);
                        if(response.status == 1){
                            seckenPrivate.setting.upgrade.lastest_version = response.data;

                            $.each(response.data, function(i, n){

                                if(dependent_code == 1){
                                    $(obj).html('<font color="red">有新版本，点击更新</font>');
                                    $(obj).attr('onclick', 'seckenPrivate.setting.upgrade.upgradeModal()');
                                }else{
                                    $(obj).html('<a href="'+n.download+'">有新版本，点击下载</a>');
                                }
                            });

                        }else{
                            $(obj).html('已是最新');
                        }
                    }
                });
            },
            upgradeModal:function(){
                $('#lastest_version_name').html(seckenPrivate.setting.upgrade.lastest_version.show_version);
                $('#lastest_version_summary').html(seckenPrivate.setting.upgrade.lastest_version.summary);
                $('#upgrade_tip').modal();
            },
            download:function(){
                $.ajax({
                    type:'post',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/web/upgrade/download',
                    data:{download:seckenPrivate.setting.upgrade.lastest_version.download},
                    jsonp:'secken_jsonp_callback',
                    beforeSend:function(){
                        $('#upgrade_tip').modal('hide');
                        $('#download').modal();
                    },
                    success:function(response){
                        seckenPrivate.jumpToLogin(response.status);
                        if(response.status == 1){
                            $('#download_tip').html('<center>压缩包已下载完成，请点击按钮进行安装</center>');
                            $('#upgrade_btn').html('开始更新');
                            $('#upgrade_btn').attr('onclick', 'seckenPrivate.setting.upgrade.update()');
                        }else{
                            $('#download_tip').html('<center>下载失败，请稍后再试！</center>');
                        }
                    }
                });
            },
            update:function(){
                $.ajax({
                    type:'post',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/web/upgrade/update',
                    data:{upgrade:seckenPrivate.setting.upgrade.lastest_version},
                    jsonp:'secken_jsonp_callback',
                    beforeSend:function(){
                        $('#download').modal('hide');
                        $('#update').modal();
                    },
                    success:function(response){
                        seckenPrivate.jumpToLogin(response.status);
                        if(response.status == 1){
                            var table = '<table width="100%;border=1">';
                                table += '<thead>';
                                table += '<tr>';
                                table += '<td width="80%">文件名</td>';
                                table += '<td width="20%">更新方式</td>';
                                table += '</tr>';
                                table += '</thead>';
                                table += '<tbody>';

                                $.each(response.data, function(i, n){

                                    var upgrade_type = n.upgrade_type == 1 ? '更改' : '新增';
                                    table += '<tr>';
                                    table += '<td width="80%">' + n.file + '</td>';
                                    table += '<td width="20%">' + upgrade_type + '</td>';
                                    table += '</tr>';
                                });
                                table += '</tbody>';
                                table += '</table>';

                                $('#update_tip').html(table);

                                $('#complate'). html('<button type="submit" class="btn btn-info pull-right" id="upgrade_btn" onclick="$(\"update\").modal("hide");"></button>');
                        }else{
                            $('#update_tip').html('<center>更新失败，请稍后再试！</center>');
                        }
                    }
                });
            }
        },
        service: {
            getServiceType:function(){
                $.ajax({
                    type:'post',
                    dataType:'jsonp',
                    url:seckenPrivate.api_url + '/web/setting/check',
                    data:{},
                    jsonp:'secken_jsonp_callback',
                    success:function(response){
                        seckenPrivate.jumpToLogin(response.status);
                        if(response.status == 1){
                            var li = '';
                            if(response.data.service_type == 1){
                                li = '<li class="list-group-item">';
                                li += '<h3>公有云</h3>';
                                li += '<span style="padding-left:3px;">使用洋葱APP进行扫描登录</span>';
                                li += '<div><button type="button" class="btn bg-olive margin" onclick="window.open(\'https://www.yangcong.com/download\')">下载洋葱APP</button></div>';
                                li += '</li>';
                            }else{
                                li = '<li class="list-group-item">';
                                li += '<h2>私有云</h2>';
                                li += '<span>使用集成洋葱SDK客户端进行扫描</span>';
                                li += '<div>';
                                li += '<button type="button" class="btn bg-olive margin" onclick="window.open(\'https://www.yangcong.com/download\')">IOS SDK 下载</button>';
                                li += '<button type="button" class="btn bg-olive margin" onclick="window.open(\'https://www.yangcong.com/download\')">Android SDK 下载</button>';
                                li += '<button type="button" class="btn bg-olive margin" onclick="window.open(\'https://www.yangcong.com/download\')">Demo</button>';
                                li += '</div>';
                                li += '</li>';
                            }

                            $('#service').html(li);
                        }
                    }
                });
            }
        }
    }
}
