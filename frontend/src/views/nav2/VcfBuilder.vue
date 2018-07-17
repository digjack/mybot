<template>
    <el-row>
        <el-col :span="12">
            <el-form ref="form" :model="form" label-width="80px" @submit.prevent="onSubmit" style="margin:20px;width:60%;min-width:600px;">
                <el-form-item label="号码列表:">
                    <el-input placeholder="姓名;手机号;标签" type="textarea" :autosize="{ minRows: 20}" v-model="form.mobile_list"></el-input>
                </el-form-item>
                <el-form-item label="">
                    <el-upload
                            class="upload-demo"
                            name="file"
                            action="/api/tool/vcf/upload"
                            :on-preview="handlePreview"
                            :on-remove="handleRemove"
                            :on-success="handleSuccess"
                            :before-remove="beforeRemove"
                            :limit="1"
                            :on-exceed="handleExceed"
                            :file-list="fileList">
                        <el-button  type="primary">点击上传</el-button>
                        <!--<div slot="tip" class="el-upload__tip">只能上传csv文件</div>-->
                    </el-upload>
                </el-form-item>
                <el-form-item label="">
                    <el-checkbox-group v-model="form.with_num">
                        <el-checkbox label="导出结果姓名前加数字前缀"  name="type" value=true ></el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item label="邀请码">
                    <el-input v-model="form.code"></el-input>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" @click="postMobile(form)">立即生成</el-button>
                    <el-button type="primary" @click="getQr()">获取邀请码(1元)</el-button>
                    <el-button @click="clear">清空</el-button>
                </el-form-item>
            </el-form>
        </el-col>
        <el-col :span="6">
            <el-card class="box-card">
                <div slot="header" class="clearfix">
                    <span>说明</span>
                    <!--<el-button style="float: right; padding: 3px 0" type="text">操作按钮</el-button>-->
                </div>
                <div  class="text item">
                    1. 支持导入csv、xlsx文件，或者直接输入号码。
                </div>
                <div  class="text item">
                    2. 表格文件分三列姓名 号码 标签 ，标签列可放空。
                </div>
                <div  class="text item">
                    3. 支付获取邀请码后，会自动填充邀请码，点击生成即可。
                </div>
                <div  class="text item">
                    4. 立即生成后，用手机微信或浏览器扫一下生成的二维码就可以下载到手机，打开文件会自动导入联系人到手机。
                </div>
                <div  class="text item">
                    5. 每个邀请码只能使用一次。
                </div>
                <div  class="text item">
                    6. 使用如有疑问或其他要求，请联系qq 244541048。
                </div>
            </el-card>
            <el-dialog
                    title="微信支付1元即可自动填充邀请码"
                    :visible.sync="show_qr"
                    width="30%"
                    :before-close="stopInterval"
                    >
                <img  style="margin: 0 auto; display: block;" :src="qr">
                <span slot="footer" class="dialog-footer">
                <!--<el-button @click="show_qr = false">取 消</el-button>-->
                <!--<el-button type="primary" @click="show_qr = false">确 定</el-button>-->
            </span>
            </el-dialog>
            <el-dialog
                    title="生成成功！手机浏览器扫描二维码即可将vcf文件下载到手机， 请确认导入后再关闭该页面！"
                    :visible.sync="show_download_dialog"
                    width="30%"
            >
                <img  style="margin: 0 auto; display: block;" :src="download_qr">
                <span slot="footer" class="dialog-footer">
                    <el-button type="primary" @click="show_download_dialog = false">完 成</el-button>
                </span>
            </el-dialog>
        </el-col>
    </el-row>

</template>

<script>
    import { transfer, qr, ListenOrderStatus} from '../../api/api';

    export default {
        data() {
            return {
                fileList: [],
                form: {
                    mobile_list:'',
                    with_num: false,
                    code: ''
                },
                qr:'',
                show_qr: false,
                download_qr:'',
                show_download_dialog:false
            };
        },
        methods: {
            handleRemove(file, fileList) {
                console.log(file, fileList);
            },
            handlePreview(file) {
                console.log(file);
            },
            handleExceed(files, fileList) {
                this.$message.warning(`当前限制选择 1 个文件，本次选择了 ${files.length} 个文件，共选择了 ${files.length + fileList.length} 个文件`);
            },
            beforeRemove(file, fileList) {
                return this.$confirm(`确定移除 ${ file.name }？`);
            },
            handleSuccess(response, file, fileList)
            {
                console.log(response);
                this.form.mobile_list = response;
            },
            clear(){
                this.form.mobile_list = '';
                this.fileList = [];
            },
            postMobile(form){
                let vm = this;
                transfer(form).then((res) => {
                    console.log(res);
                    if(res.type === 'qr'){
                        vm.show_download_dialog = true;
                        vm.download_qr = res.data;
                    }else{

//                        const url = window.URL.createObjectURL(new Blob([res]));
//                        const link = document.createElement('a');
//                        link.href = url;
//                        console.log(res);
//                        let fname = 'output.vcf';
//                        link.setAttribute('download', fname); //or any other extension
//                        document.body.appendChild(link);
//                        link.click();
                    }
                }).catch(( error ) => {
                    this.$message('邀请码校验错误！');
                });
            },
            stopInterval(done){
                clearInterval(this.payStatusIntervalId);
                done();
            },
            getQr(){
                let vm = this;
                qr().then((res)=> {
                    vm.qr = res.data.qr_code;
                    vm.qr_id = res.data.qr_id;
                    vm.show_qr = true;
                    vm.listenPayResultInterval(res.data.qr_id);

                });
            },
            listenPayResultInterval: function (qrId) {
                const self = this;
                this.payStatusIntervalId = setInterval(function(){
                    let para = {
                        qr_id: qrId
                    };
                    ListenOrderStatus(para).then((res) => {
                        if(res.data.status == 'success'){
                            console.log(res);
                            self.show_qr = false;
                            self.form.code = res.data.code;
                            self.$message( "支付成功, 邀请码为: " + vm.form.code);
                            clearInterval(self.payStatusIntervalId);
                        }
                    }).catch((error) => {
                        self.show_qr = false;
                        clearInterval(self.payStatusIntervalId);
                    });
                }, 2000);
            },
        }
    }

</script>

<style>
    .text {
        font-size: 14px;
    }

    .item {
        margin-bottom: 18px;
    }

    .clearfix:before,
    .clearfix:after {
        display: table;
        content: "";
    }
    .clearfix:after {
        clear: both
    }

    .box-card {
        width: 370px;
    }
</style>