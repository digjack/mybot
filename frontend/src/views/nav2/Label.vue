<template>
    <section>
        <!--工具条-->
        <el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
            <el-form :inline="true" :model="filters">
                <el-form-item>
                    <el-input v-model="filters.name" placeholder="昵称"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleAdd">新建标签</el-button>
                </el-form-item>
                <el-popover
                        placement="top-start"
                        title="注意事项"
                        width="600"
                        trigger="hover">
                    <p>1. 分组标签只在本网站内有效，没有与微信的标签功能同步</p>
                    <p>2. 如类型选择备注，则备注为空的好友不会发送消息。</p>
                    <p>3. 如有疑问，咨询qq 244541048</p>
                    <i slot="reference" class="el-icon-question"></i>
                </el-popover>
            </el-form>
        </el-col>

        <!--列表-->
        <el-table :data="label_list" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
            <el-table-column type="index" width="60">
            </el-table-column>
            <el-table-column prop="name" label="标签" width="150" sortable>
            </el-table-column>
            <el-table-column prop="member_count" label="用户数" width="150" sortable>
            </el-table-column>
            <el-table-column prop="type" :formatter="formatGroupType" label="组类型" width="200" sortable>
            </el-table-column>
            <el-table-column label="操作" width="300">
                <template scope="scope">
                    <el-button type="danger" size="small"  @click="handleList(scope.row)">查看用户</el-button>
                    <el-button type="danger" size="small"  @click="handleEdit(scope.row)">编辑</el-button>
                    <el-button type="danger" size="small"  @click="handleDel(scope.row)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <!--工具条-->
        <el-col :span="24" class="toolbar">
            <el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="20" :total="total" style="float:right;">
            </el-pagination>
        </el-col>



        <!--新增界面-->
        <el-dialog title="新增" :visible.sync="addFormVisible" :close-on-click-modal="false" custom-class="dialog-center">
            <el-form :model="addForm" label-width="80px" ref="addForm">
                <el-form-item label="标签名" prop="name">
                    <el-input v-model="addForm.name"></el-input>
                </el-form-item>
                <el-form-item label="标签类型">
                    <el-radio-group  v-model="addForm.type">
                        <el-radio label="1" >依据昵称</el-radio>
                        <el-radio label="2" >依据备注</el-radio>
                        <el-radio label="3" >微信群名</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="addFormVisible = false">取消</el-button>
                <el-button type="primary" @click.native="addSubmit" :loading="addLoading">提交</el-button>
            </div>
        </el-dialog>


        <!--编辑界面-->
        <el-dialog title="编辑" :visible.sync="editFormVisible" :close-on-click-modal="false" custom-class="dialog-center">
            <el-form :model="editForm" label-width="80px" ref="editForm">
                <el-form-item label="标签名" prop="name">
                    <el-input v-model="editForm.name"></el-input>
                </el-form-item>

                <el-form-item label="标签类型">
                    <el-radio-group  v-model="editForm.type">
                        <el-radio label="1" >依据昵称</el-radio>
                        <el-radio label="2" >依据备注</el-radio>
                        <el-radio label="3" >微信群名</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="editFormVisible = false">取消</el-button>
                <el-button type="primary" @click.native="editSubmit" :loading="addLoading">保存</el-button>
            </div>
        </el-dialog>

        <el-dialog title="成员列表" :visible.sync="groupDialogVisible" :close-on-click-modal="false" custom-class="dialog-center">
            <el-table
                    :data="groupUsers"
                    style="width: 100%">
                <el-table-column
                        prop="nick_name"
                        label="昵称"
                        width="180">
                </el-table-column>
                <el-table-column
                        prop="remark_name"
                        label="备注"
                        width="180">
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button
                                size="mini"
                                type="danger"
                                @click="handleDeleteUser(scope.$index, scope.row)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>
    </section>
</template>

<script>
    import util from '../../common/js/util'
    import { Message } from 'element-ui';
    import { getLabelList, saveLabel, delLabel,  listMember, addMember, delMember} from '../../api/api';

    export default {
        data() {
            return {
                groupDialogVisible: false,
                groupUsers: [
                    {nick_name: 'a', remark_name: 'c'}
                ],
                filters: {
                    name: ''
                },
                users: [],
                label_list:[],
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
                addForm: {},
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
//            handleCurrentChange(val) {
//                this.page = val;
//                this.getTasks();
//            },
            //获取任务列表
            getLabels() {
                let para = {
                    name: this.filters.name,
                    type: [1,2,3]
                };
                this.listLoading = true;
                getLabelList(para).then((res) => {
                    this.total = res.data.total;
                    this.label_list = res.data.list;
                    this.listLoading = false;
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
                    delLabel(para).then((res) => {
                        this.listLoading = false;
                        //NProgress.done();
                        this.$message({
                            message: '删除成功',
                            type: 'success'
                        });
                        this.getLabels();
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
                    type: '1'
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
                            saveLabel(para).then((res) => {
                                this.editLoading = false;
                                //NProgress.done();
                                this.$message({
                                    message: '保存成功',
                                    type: 'success'
                                });
                                this.$refs['editForm'].resetFields();
                                this.editFormVisible = false;
                                this.getLabels();
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
                            saveLabel(para).then((res) => {
                                this.addLoading = false;
                                //NProgress.done();
                                this.$message({
                                    message: '保存成功',
                                    type: 'success'
                                });
                                this.$refs['addForm'].resetFields();
                                this.addFormVisible = false;
                                this.getLabels();
                            });
                        });
                    }
                });
            },
            handleList: function (row) {
                let para = {
                    label_id : row.id
                };
                let vm = this;
                listMember(para).then((res) => {
                    console.log(res);
                    vm.groupDialogVisible = true;
                    vm.groupUsers = res.data.list;
                });
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
            handleDeleteUser: function (index, row) {
                let para = {
                    id : row.id,
                    label_id: row.group_id
                };
                delMember(para).then((res) => {
                    if(res.status === true){
                        this.$message({
                            message: '删除成功',
                            type: 'success'
                        });
                    }else{
                        this.$message({
                            message: '删除失败',
                            type: 'fail'
                        });
                    }

                    let params = {
                        label_id : row.group_id
                    };
                    let vm = this;
                    listMember(params).then((res) => {
                        vm.groupDialogVisible = true;
                        vm.groupUsers = res.data.list;
                    });
                });
            },
            formatGroupType: function (row, column, value) {
                if(value === 1 || value === '1'){
                    return '依据昵称';
                }
                if (value === 2 || value === '2'){
                    return '依据备注';
                }
                if (value === 3 || value === '3'){
                    return '微信群名';
                }
                return  value;

            }
        },
        mounted() {
            this.getLabels();
        }
    }

</script>

<style>

</style>
