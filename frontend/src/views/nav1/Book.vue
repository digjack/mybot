<template>
    <section>
        <!--工具条-->
        <el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
            <el-form :inline="true" :model="filters">
                <el-form-item>
                    <el-input v-model="filters.name" placeholder="文件名"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" v-on:click="getSources">查询</el-button>
                    <el-popover
                            placement="top-start"
                            title="购买流程"
                            width="200"
                            trigger="hover"
                            content= "点击网盘密码 -> 输入接收邮箱后确认 -> 扫码支付后链接和密码自动发送到邮箱;  使用过程如有疑问，请联系站长qq 244541048 ">
                        <i slot="reference" class="el-icon-question"></i>
                    </el-popover>
                </el-form-item>
                <el-form-item v-if="isAdmin">
                    <el-button type="primary" @click="handleAdd">新增</el-button>
                </el-form-item>
            </el-form>
        </el-col>

        <!--列表-->
        <el-table :data="resource_list" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
            <el-table-column type="index" width="60">
            </el-table-column>
            <el-table-column prop="name" label="文件名" width="400" sortable>
            </el-table-column>
            <el-table-column prop="size" label="大小" width="200"  sortable>
            </el-table-column>
            <el-table-column prop="price" label="价格" width="200" sortable>
            </el-table-column>
            <!--<el-table-column prop="detail" label="目录" min-width="200" sortable>-->
            <!--</el-table-column>-->
            <!--<el-table-column prop="count" label="下载次数" min-width="100" sortable>-->
            <!--</el-table-column>-->
            <el-table-column label="操作" :width="(isAdmin == true)?500:270">
                <template scope="scope">
                    <el-button size="small" @click="handleDownload(scope.row)">下载</el-button>
                    <el-button v-if="isAdmin"size="small"  @click="handleEdit(scope.row)">编辑</el-button>
                    <el-button v-if="isAdmin"size="small"  @click="handleDel(scope.row)">删除</el-button>
                    <el-button type="danger" size="small"  @click="payPopup(scope.row)">网盘密码</el-button>
                    <el-button type="danger" size="small"  @click="detailPopup(scope.row)">目录</el-button>
                </template>
            </el-table-column>
        </el-table>

        <!--工具条-->
        <el-col :span="24" class="toolbar">
            <el-button v-if="false" type="danger" @click="batchRemove" :disabled="this.sels.length===0 || true">批量删除</el-button>
            <el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="20" :total="total" style="float:right;">
            </el-pagination>
        </el-col>

        <!--下单modal-->
        <el-dialog title="获取密码" :visible.sync="getCodeModal" width="30%" custom-class="dialog-center" @close="clearOrder()">
            <el-form :model="currentRow">
                <el-form-item label="文件名：" label-width="100px">
                    <span auto-complete="off">{{currentRow.name}}</span>
                </el-form-item>
                <el-form-item label="价格：" label-width="100px">
                    <span  auto-complete="off">{{currentRow.price}}</span>
                </el-form-item>
                <el-form-item label="接收邮箱:" label-width="100px">
                    <el-input v-model="currentRow.email" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item v-if="qrCode" label="微信支付:" label-width="100px">
                    <img  :src="qrCode"></img>
                </el-form-item>
                <el-form-item v-if="order_password" label="网盘密码:" label-width="100px">
                    <span auto-complete="off">{{order_password}}</span>
                </el-form-item>

            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="clearOrder()">关 闭</el-button>
                <el-button type="primary" @click="order(currentRow)" :loading="is_paying">{{payButtonText}}</el-button>
            </div>
        </el-dialog>

        <!--新增界面-->
        <el-dialog title="新增" :visible.sync="addFormVisible" :close-on-click-modal="false" custom-class="dialog-center">
            <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
                <el-form-item label="文件名" prop="name">
                    <el-input v-model="addForm.name" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="大小">
                    <el-input v-model="addForm.size" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="价格">
                    <el-input v-model="addForm.price" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="密码">
                    <el-input v-model="addForm.password" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="下载链接">
                    <el-input v-model="addForm.download_url" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="目录">
                    <el-input type="textarea" v-model="addForm.detail"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="addFormVisible = false">取消</el-button>
                <el-button type="primary" @click.native="addSubmit" :loading="addLoading">提交</el-button>
            </div>
        </el-dialog>




        <!--编辑界面-->
        <el-dialog title="编辑" :visible.sync="editFormVisible" :close-on-click-modal="false" custom-class="dialog-center">
            <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
                <el-form-item label="文件名" prop="name">
                    <el-input v-model="editForm.name" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="大小">
                    <el-input v-model="editForm.size" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="价格">
                    <el-input v-model="editForm.price" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="密码">
                    <el-input v-model="editForm.password" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="下载链接">
                    <el-input v-model="editForm.download_url" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="目录">
                    <el-input type="textarea" v-model="editForm.detail"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="editFormVisible = false">取消</el-button>
                <el-button type="primary" @click.native="editSubmit" :loading="addLoading">提交</el-button>
            </div>
        </el-dialog>

        <!--详情页-->
        <el-dialog :title="currentRow.title" :visible.sync="showDetail" :close-on-click-modal="false">
            <el-form  label-width="80px">
                <el-form-item label="目录">
                    <el-input type="textarea" :autosize="{ minRows: 25, maxRows: 25}" v-model="currentRow.detail" :disabled="true"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="showDetail = false">关闭</el-button>
            </div>
        </el-dialog>

    </section>
</template>

<script>
    import util from '../../common/js/util'
    import { Message } from 'element-ui';
    import { getSourceList, getUserListPage, removeUser, batchRemoveUser, editUser, addUser } from '../../api/api';
    import { payOrder, ListOrderStatus, saveResource, deleteResource} from '../../api/api';

    export default {
        data() {
            return {
                filters: {
                    name: ''
                },
                users: [],
                resource_list:[],
                total: 0,
                page: 1,
                listLoading: false,
                sels: [],//列表选中列

                editFormVisible: false,//编辑界面是否显示
                editLoading: false,
                editFormRules: {
                    name: [
                        { required: true, message: '请输入姓名', trigger: 'blur' }
                    ]
                },
                //编辑界面数据
                editForm: {},

                addFormVisible: false,//新增界面是否显示
                addLoading: false,
                addFormRules: {
                    name: [
                        { required: true, message: '请输入姓名', trigger: 'blur' }
                    ]
                },
                //新增界面数据
                addForm: {
                    name: '',
                    sex: -1,
                    age: 0,
                    birth: '',
                    addr: ''
                },
                getCodeModal: false,
                currentRow:{},
                qrCode: '',
                qrId: 0,
                order_password: '',
                payStatusIntervalId: 0,
                isAdmin:false,
                showDetail: false,
                payButtonText:'确定',
                is_paying: false
            }
        },
        methods: {
            handleCurrentChange(val) {
                this.page = val;
                this.getSources();
            },
            //获取用户列表
            getSources() {
                let para = {
                    page: this.page,
                    name: this.filters.name,
                    type: 'book',
                    is_admin: this.isAdmin
                };
                this.listLoading = true;
                //NProgress.start();
                getSourceList(para).then((res) => {
                    this.total = res.data.total;
                    this.resource_list = res.data.list;
                    this.listLoading = false;
                    //NProgress.done();
                });
            },
            //删除
            handleDel: function (row) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    //NProgress.start();
                    let para = { id: row.id };
                    deleteResource(para).then((res) => {
                        this.listLoading = false;
                        //NProgress.done();
                        this.$message({
                            message: '删除成功',
                            type: 'success'
                        });
                        this.getSources();
                    });
                }).catch(() => {

                });
            },
            //显示编辑界面
            handleEdit: function (row) {
                this.editFormVisible = true;
                this.editForm = Object.assign({}, row);
            },
            //显示新增界面
            handleAdd: function () {
                this.addFormVisible = true;
                this.addForm = {
                    name: '',
                    size: 0,
                    price: '',
                    detail: '',
                    download_url: '',
                    password: 0,
                    type: "book"
                };
            },
            //编辑
            editSubmit: function () {
                this.$refs.editForm.validate((valid) => {
                    if (valid) {
                        this.$confirm('确认提交吗？', '提示', {}).then(() => {
                            this.editLoading = true;
                            //NProgress.start();
                            let para = Object.assign({}, this.editForm);
                            saveResource(para).then((res) => {
                                this.editLoading = false;
                                //NProgress.done();
                                this.$message({
                                    message: '提交成功',
                                    type: 'success'
                                });
                                this.$refs['editForm'].resetFields();
                                this.editFormVisible = false;
                                this.getSources();
                            });
                        });
                    }
                });
            },
            //新增
            addSubmit: function () {
                this.$refs.addForm.validate((valid) => {
                    if (valid) {
                        this.$confirm('确认提交吗？', '提示', {}).then(() => {
                            this.addLoading = true;
                            //NProgress.start();
                            let para = Object.assign({}, this.addForm);
                            saveResource(para).then((res) => {
                                this.addLoading = false;
                                //NProgress.done();
                                this.$message({
                                    message: '提交成功',
                                    type: 'success'
                                });
                                this.$refs['addForm'].resetFields();
                                this.addFormVisible = false;
                                this.getSources();
                            });
                        });
                    }
                });
            },

            handleDownload: function (row) {
                window.open(row.download_url, "_blank");
            },
            selsChange: function (sels) {
                this.sels = sels;
            },
            //批量删除
            batchRemove: function () {
                var ids = this.sels.map(item => item.id).toString();
                this.$confirm('确认删除选中记录吗？', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    //NProgress.start();
                    let para = { ids: ids };
                    batchRemoveUser(para).then((res) => {
                        this.listLoading = false;
                        //NProgress.done();
                        this.$message({
                            message: '删除成功',
                            type: 'success'
                        });
                        this.getUsers();
                    });
                }).catch(() => {

                });
            },
            detailPopup: function (row) {
                this.currentRow = row;
                this.showDetail = true;
            },
            payPopup: function(currentRow) {
                this.currentRow = currentRow;
                console.log(currentRow);
                this.getCodeModal = true;
            },
            clearOrder: function () {
                this.getCodeModal = false;
                this.qrCode = '';
                this.qrId = '';
                this.order_password = '';
                this.currentRow = {};
                clearInterval(this.payStatusIntervalId);
            },
            order: function (row) {
                //todo 提交订单
                let para = {
                    id: row.id,
                    email: row.email
                };
                //NProgress.start();
                payOrder(para).then((res) => {
                    console.log(res);
                    this.qrCode = res.qr_code;
                    this.qrId = res.qr_id;
                    this.payButtonText = "等待支付";
                    this.is_paying = true;
                    this.listenPayResultInterval(this.qrId);
                });

            },
            listenPayResultInterval: function (qrId) {
                const self = this;
                this.payStatusIntervalId = setInterval(function(){
                    let para = {
                        qr_id: qrId
                    };
                    ListOrderStatus(para).then((res) => {
                        console.log(res.data.status);
                        if(res.data.status == 'success'){
                            self.order_password = res.data.password;
                            self.payButtonText = "支付成功";
                            self.is_paying = false;
                            clearInterval(self.payStatusIntervalId);
                            Message({
                                message: "支付成功"
                            });
                        }
                    }).catch((error) => {
                        console.log(error);
                        clearInterval(self.payStatusIntervalId);

                    });
                }, 2000);
            },
            checkIsAdmin: function (){
                console.log(this.$route.query);
                if(this.$route.query.code){
                    var code = this.$route.query.code;
                    if(code == '320'){
                        this.isAdmin = true;
                    }
                }
            }
        },
        mounted() {
            this.checkIsAdmin();
            this.getSources();
        }
    }

</script>

<style>

</style>
