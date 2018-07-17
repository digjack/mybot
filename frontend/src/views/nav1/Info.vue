<template>
    <div>

    <el-form ref="form"  label-width="80px" style="margin:20px;width:60%;min-width:600px;">
        <el-form-item label="微信昵称: ">
            <span>{{vbot.myself.nickname}}</span>
        </el-form-item>
        <el-form-item label="当前状态:">
            <span v-if="vbot.status == 'success'">正常运行</span>
            <span v-else>未登录</span>
        </el-form-item>
        <el-form-item>
            <el-button type="primary" @click="showQr('normal')" :loading="refreshLoading">刷新授权</el-button>
            <el-button type="primary" @click="showQr('force')" :loading="loginLoading">登录账号</el-button>
            <el-button type="primary" @click="testMsg()" :loading="testMsgLoading">测试发送</el-button>
            <el-button type="primary" @click="clearAuth()" >清除授权</el-button>
        </el-form-item>
    </el-form>

    <el-dialog
            title="微信扫一扫登录"
            :visible.sync="qr_status"
            @close="closeQr()"
    >
        <img  style="margin: 0 auto; display: block;" :src="qr_code" />
        <!--<span slot="footer" class="dialog-footer">-->
        <!--<el-button type="primary" @click="qr_code = false">关闭</el-button>-->
        <!--</span>-->
    </el-dialog>

        <el-dialog title="发送成功！" :visible.sync="testDialog" :close-on-click-modal="false" custom-class="dialog-center">
            <p>如您在微信里的文件传输助手收到下面的消息，则授权正常。</p>
            <p>{{testDialogMsg}}</p>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="testDialog = false">关闭</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import { Message } from 'element-ui';
    import { initVbot, qr, getStatus, testMsg , clearAuth} from '../../api/api';

    export default {
        data() {
            return {
                testMsgLoading: false,
                refreshLoading: false,
                loginLoading:false,
                qr_status: false,
                qr_code: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAG8AAABvAQMAAADYCwwjAAAABlBMVEX///8AAABVwtN+AAAACXBIWXMAAA7EAAAOxAGVKw4bAAABI0lEQVQ4jdXTQa6EIAwA0BIW7OACJlzDHVcaL+DIBeRK7LgGCRfQHQtjfzX++atP2Q4xhmdi2tIC8I3LIPp4gJKImaUGubhsUlmgg66EiivQu4se7aZ6uYyUm+wiSB/t6eQnyQap3nUs9HzKb/D6UnGP+e8w/yftTwdTQsqNpUgDOBmi9ffvbcJY9jhM9ZgdT01lquEKp4CliXeDUvEpswR3zKqsgE9WTQqkiUKkxFIHY37XQ6D1CViaZAO1PuGTZJOiDgbtCcVXnlrJE+QGuCmeArPBYbpiAUs9SgoxIZ7A846VX5C1yyxpYkM6pkRt4kl3YR2Rbo2JwNOVVWURfyeH43Jv5rGLoR4a4H2PCkOQAe2e8usZ4Baveil/fHrU5vetH2W4CHvYhBuaAAAAAElFTkSuQmCC',
                vbot:{
                    qr: 'xiaodong',
                    status : 'wait',
                    myself : {
                        nickname : '未知..'
                    }
                },
                intervalId:'',
                testDialog: false,
                testDialogMsg: ''
            }
        },
        methods: {
            init: function() {
                let vm = this;
                initVbot().then((res) => {
                    console.log(res);
                    if(! res.data.status || res.data.status !== 'success'){
                        vm.showQr('normal');
                    }else{
                        vm.vbot = res.data;
                    }
                });
            },
            showQr: function(type){
                if(type === 'normal'){
                    this.refreshLoading = true;
                }else {
                    this.loginLoading = true;
                }

                var vm = this;
                let para = {
                    type: type
                };
                qr(para).then((res) => {
                    vm.refreshLoading = false;
                    vm.loginLoading = false;
                    if(res.data.status && res.data.status === 'success' && ! res.data.qr){
                        Message({
                            message: "免扫码登录成功..",
                            type: 'success'
                        });
                    }
                    if(res.data.status && res.data.status === 'failed'){
                        Message({
                            message: "二维码获取失败，请联系客服",
                            type: 'fail'
                        });
                    }
                    if(res.data.status && res.data.status === 'success' && res.data.qr){
                        this.qr_code = res.data.qr;
                        this.qr_status = true;
                        this.listenStatus();
                    }
                });
            },
            listenStatus:function(){
                let vm = this;
                let x =0;
                this.intervalId = setInterval(function () {
                    console.log(x);
                    if (++x === 30) {
                        window.clearInterval(vm.intervalId);
                    }
                    getStatus().then((res) => {
                        if(res.data.status){
                            vm.qr_status = false;
                            Message({
                                message: "登录成功..",
                                type: 'success'
                            });
                            clearInterval(vm.intervalId);
                            vm.init();
                        }
                    })
                }, 3000)
            },
            closeQr: function () {
                this.qr_status = false;
                clearInterval(this.intervalId);
            },
            testMsg: function () {
                let vm = this;
                vm.testMsgLoading = true;
                testMsg().then((res) => {
                    console.log(res);
                    if (res.data.status && res.data.status === true) {
                        vm.testDialog = true;
                        vm.testMsgLoading = false;
                        vm.testDialogMsg = res.data.msg;
                    }
                });
            },
            clearAuth: function () {
                let vm = this;
                clearAuth().then((res) => {
                    Message({
                        message: "清除完成..",
                        type: 'success'
                    });
                });
            }
        },
        mounted() {
            this.init();
        }
    }

</script>