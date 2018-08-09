<template>
    <section>
        <!--工具条-->
        <el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
            <el-form :inline="true" :model="filters">
                <el-form-item>
                    <el-input v-model="filters.name" placeholder="文件名"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleAdd">新建计划</el-button>
                </el-form-item>
                <el-popover
                        placement="top-start"
                        title="注意事项"
                        width="600"
                        trigger="hover">
                    <p>1. 分组标签只在本网站内有效，没有与微信的标签功能同步</p>
                    <p>2. 支持定时发送</p>
                    <p>3. 如有疑问，咨询qq 2039399031</p>
                    <i slot="reference" class="el-icon-question"></i>
                </el-popover>

            </el-form>
        </el-col>

        <!--列表-->
        <el-table :data="task_list" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
            <el-table-column type="index" width="60">
            </el-table-column>
            <el-table-column prop="name" label="计划名" width="150" sortable>
            </el-table-column>
            <el-table-column prop="send_time" label="发送时间" width="150" sortable>
            </el-table-column>
            <el-table-column prop="num" label="发送数" width="120" sortable>
            </el-table-column>
            <el-table-column prop="type" label="类型" width="120" sortable>
            </el-table-column>
            <el-table-column prop="params" label="参数" width="120" sortable>
            </el-table-column>
            <el-table-column prop="content" label="内容" width="200" sortable>
            </el-table-column>
            <el-table-column prop="status" :formatter="formatStatus" label="状态" width="100" sortable>

            </el-table-column>
            <el-table-column label="操作" width="270">
                <template scope="scope">
                    <el-button type="danger" v-if="scope.row.status == 0" size="small"  @click="cancelPlan(scope.row)">取消</el-button>
                    <el-button type="danger" v-if="scope.row.status == 0" size="small"  @click="confirmPlan(scope.row)">确认发送</el-button>
                </template>
            </el-table-column>
        </el-table>

        <!--工具条-->
        <el-col :span="24" class="toolbar">
            <el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="20" :total="total" style="float:right;">
            </el-pagination>
        </el-col>

        <!--新建界面-->
        <el-dialog title="新建" :visible.sync="addFormVisible" :close-on-click-modal="false" custom-class="dialog-center">
            <el-form :model="addForm" label-width="80px" ref="addForm">
                <el-form-item label="计划名" prop="name">
                    <el-input v-model="addForm.name"></el-input>
                </el-form-item>
                <el-form-item label="发送时间">
                    <el-input id='send_time' v-model="addForm.send_time"></el-input>
                </el-form-item>
                <el-form-item label="性别">
                    <el-radio-group  v-model="addForm.sex">
                        <el-radio label="all" >不限</el-radio>
                        <el-radio label="male" >男</el-radio>
                        <el-radio label="female" >女</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="省份">
                    <el-radio-group  v-model="addForm.province">
                        <el-radio label="all" >不限</el-radio>
                        <el-radio label="male" >男</el-radio>
                        <el-radio label="female" >女</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item  label="本地标签">
                    <el-select v-model="addForm.label" placeholder="不限">
                        <el-option v-for="group in label_list" :label="group.name" :value="group.id"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="发送内容">
                    <el-input type="textarea" v-model="addForm.content" auto-complete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="addFormVisible = false">取消</el-button>
                <el-button @click.native="countNum" :loading="countLoading">计算人数</el-button>
                <el-button type="primary" @click.native="addSubmit" :loading="addLoading">提交</el-button>
            </div>
        </el-dialog>
    </section>
</template>

<script>
    import util from '../../common/js/util'
    import { Message } from 'element-ui';
    import { getTaskList, saveTask, delTask,  removeUser, batchRemoveUser, editUser, addUser, getLabelList } from '../../api/api';

    export default {
        data() {
            return {
                descCycle: false,
                filters: {
                    name: ''
                },
                users: [],
                task_list:[],
                total: 0,
                page: 1,
                listLoading: false,
                sels: [],//列表选中列
                label_list: [
                    {id: 1, name: '牛逼的分组'},
                    {id: 2, name: '小小的分组'}
                ],

                addFormVisible: false,//新增界面是否显示
                addLoading: false,
                addFormRules: {
                    name: [
                        { required: true, message: '请输入姓名', trigger: 'blur' }
                    ]
                },
                //新增界面数据
                addForm: {},
                currentRow:{},
                isAdmin:false,
                showDetail: false,
                payButtonText:'确定'
            }
        },
        methods: {
            handleCurrentChange(val) {
                this.page = val;
                this.getTasks();
            },
            //获取任务列表
            getTasks() {
                let para = {
                    name: this.filters.name,
                };
                this.listLoading = true;
                getTaskList(para).then((res) => {
                    this.total = res.data.total;
                    this.task_list = res.data.list;
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
                    delTask(para).then((res) => {
                        this.listLoading = false;
                        //NProgress.done();
                        this.$message({
                            message: '删除成功',
                            type: 'success'
                        });
                        this.getTasks();
                    });
                }).catch(() => {

                });
            },
            pauseTask: function(row){
                this.listLoading = true;
                row.status = 1;
                let para = row;
                saveTask(para).then((res) => {
                    this.listLoading = false;
                    //NProgress.done();
                    this.$message({
                        message: '暂停成功',
                        type: 'success'
                    });
                    this.getTasks();
                });
            },
            restartTask: function(row){
                this.listLoading = true;
                row.status = 2;
                let para = row;
                saveTask(para).then((res) => {
                    this.listLoading = false;
                    //NProgress.done();
                    this.$message({
                        message: '暂停成功',
                        type: 'success'
                    });
                    this.getTasks();
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
                    receiver_type: 'friend'
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
                            saveTask(para).then((res) => {
                                this.editLoading = false;
                                //NProgress.done();
                                this.$message({
                                    message: '保存成功',
                                    type: 'success'
                                });
                                this.$refs['editForm'].resetFields();
                                this.editFormVisible = false;
                                this.getTasks();
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
                            saveTask(para).then((res) => {
                                this.addLoading = false;
                                //NProgress.done();
                                this.$message({
                                    message: '保存成功',
                                    type: 'success'
                                });
                                this.$refs['addForm'].resetFields();
                                this.addFormVisible = false;
                                this.getTasks();
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
            bindDateCycle: function(str){
                document.getElementById('add_date_cycle').value = str;
                console.log(str);
                this.addForm.date_cycle = str;
            },
            bindEditDateCycle: function(str){
                document.getElementById('edit_date_cycle').value = str;
                console.log(str);
                this.editForm.date_cycle = str;
            },
            listLabels: function(str){
                let para = {
                    type:[1,2,3]
                };
                getLabelList(para).then((res) => {
                    this.label_list = res.data.list;
                });
            },
            formatReceiverType: function(row, column, cellValue, index){
                if(cellValue === 'group'){
                    return '分组';
                }
                return '好友';
            },
            formatReceiver: function(row, column, cellValue, index){
                if(row['receiver_type'] == 'group'){
                    return row['group_name'];
                }
                return row['receiver'];
            },
            formatStatus:function(row, column, cellValue, index){
                if(cellValue === 2 || cellValue === '2'){
                    return '正常';
                }
                return '暂停';
            }
        },
        mounted() {
            this.getTasks();
            this.listLabels();
        }
    }

</script>

<style>

</style>
