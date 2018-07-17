<template>
    <section>
        <!--工具条-->
        <el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
            <el-form :inline="true" :model="filters">
                <el-form-item>
                    <el-input v-model="filters.name" placeholder="文件名"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleAdd">新建任务</el-button>
                </el-form-item>
                <el-popover
                        placement="top-start"
                        title="注意事项"
                        width="600"
                        trigger="hover">
                    <p>1. 分组标签只在本网站内有效，没有与微信的标签功能同步</p>
                    <p>2. 如果只需发送一次，则发送次数设置为1即可</p>
                    <p>3. 如有疑问，咨询qq 244541048</p>
                    <i slot="reference" class="el-icon-question"></i>
                </el-popover>

            </el-form>
        </el-col>

        <!--列表-->
        <el-table :data="task_list" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
            <el-table-column type="index" width="60">
            </el-table-column>
            <el-table-column prop="name" label="任务名" width="150" sortable>
            </el-table-column>
            <el-table-column prop="date_cycle" label="周期" width="150" sortable>
            </el-table-column>
            <el-table-column prop="next_date" label="下次发送时间" width="200" sortable>
            </el-table-column>
            <el-table-column prop="success_count" label="发送成功次数" width="120" sortable>
            </el-table-column>
            <el-table-column prop="max_count" label="预设发送次数" width="120" sortable>
            </el-table-column>
            <el-table-column prop="receiver_type" :formatter="formatReceiverType" label="接收类型" width="100"  sortable>
            </el-table-column>
            <el-table-column prop="receiver"  :formatter="formatReceiver" label="接收者" width="150"  sortable>
            </el-table-column>
            <el-table-column prop="content" label="内容" width="200" sortable>
            </el-table-column>
            <el-table-column prop="status" :formatter="formatStatus" label="状态" width="100" sortable>

            </el-table-column>
            <el-table-column label="操作" width="270">
                <template scope="scope">
                    <el-button type="danger" size="small"  @click="handleEdit(scope.row)">编辑</el-button>
                    <el-button type="danger" v-if="scope.row.status == 2" size="small"  @click="pauseTask(scope.row)">暂停</el-button>
                    <el-button type="danger" v-if="scope.row.status != 2" size="small"  @click="restartTask(scope.row)">重启</el-button>
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
                <el-form-item label="任务名" prop="name">
                    <el-input v-model="addForm.name"></el-input>
                </el-form-item>
                <el-form-item label="周期">
                    <el-input id='add_date_cycle' v-model="addForm.date_cycle"></el-input>
                    <el-tag  @click.native="bindDateCycle('00 20 * * *')">每天晚上八点</el-tag>
                    <el-tag  @click.native="bindDateCycle('00 10 * * 6')">每周六上午10点</el-tag>
                    <el-tag  @click.native="bindDateCycle('00 10 * * 1,2,3,4,5')">每个工作日上午10点</el-tag>
                    <el-tag  @click.native="bindDateCycle('00 10 03 * *')">每月3号 上午8点</el-tag>
                    <el-button type="primary" @click.native="descCycle=true" size="small">其他</el-button>
                </el-form-item>
                 <el-form-item label="接收类型">
                    <el-radio-group  v-model="addForm.receiver_type">
                      <el-radio label="friend" >微信好友</el-radio>
                      <el-radio label="group" >分组好友</el-radio>
                    </el-radio-group>
                  </el-form-item>
                <el-form-item v-if="addForm.receiver_type === 'friend'" label="接收者">
                    <el-input type="textarea" v-model="addForm.receiver" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item v-if="addForm.receiver_type === 'group'" label="接收分组">
                       <el-select v-model="addForm.receiver" placeholder="请选择分组">
                             <el-option v-for="group in label_list" :label="group.name" :value="group.id"></el-option>
                       </el-select>
                </el-form-item>
                <el-form-item label="发送内容">
                    <el-input type="textarea" v-model="addForm.content" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="发送次数">
                    <el-input v-model="addForm.max_count" auto-complete="off"></el-input>
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
                        <el-form-item label="任务名" prop="name">
                            <el-input v-model="editForm.name"></el-input>
                        </el-form-item>
                        <el-form-item label="周期">
                            <el-input id='edit_date_cycle' v-model="editForm.date_cycle"></el-input>
                            <el-tag  @click.native="bindEditDateCycle('00 20 * * *')">每天晚上八点</el-tag>
                            <el-tag  @click.native="bindEditDateCycle('00 10 * * 6')">每周六上午10点</el-tag>
                            <el-tag  @click.native="bindEditDateCycle('00 10 * * 1,2,3,4,5')">每个工作日上午10点</el-tag>
                            <el-tag  @click.native="bindEditDateCycle('00 10 03 * *')">每月3号 上午8点</el-tag>
                        </el-form-item>
                         <el-form-item label="接收类型">
                            <el-radio-group  v-model="editForm.receiver_type">
                              <el-radio label="friend" >微信好友</el-radio>
                              <el-radio label="group" >分组好友</el-radio>
                            </el-radio-group>
                          </el-form-item>
                        <el-form-item v-if="editForm.receiver_type === 'friend'" label="接收者">
                            <el-input type="textarea" v-model="editForm.receiver" auto-complete="off"></el-input>
                        </el-form-item>
                        <el-form-item v-if="editForm.receiver_type === 'group'" label="接收分组">
                               <el-select v-model="editForm.receiver" placeholder="请选择分组">
                                     <el-option v-for="group in label_list" :label="group.name" :value="group.id"></el-option>
                               </el-select>
                        </el-form-item>
                        <el-form-item label="发送内容">
                            <el-input type="textarea" v-model="editForm.content" auto-complete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="发送次数">
                            <el-input v-model="editForm.max_count" auto-complete="off"></el-input>
                        </el-form-item>
                    </el-form>
                    <div slot="footer" class="dialog-footer">
                        <el-button @click.native="editFormVisible = false">取消</el-button>
                        <el-button type="primary" @click.native="editSubmit" :loading="addLoading">保存</el-button>
                    </div>
                </el-dialog>
                <el-dialog title="周期说明" :visible.sync="descCycle" :close-on-click-modal="false">
                    <p>分&nbsp&nbsp时&nbsp&nbsp日&nbsp&nbsp月&nbsp&nbsp周几</p>
                    <p>*&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp&nbsp&nbsp每分钟</p>
                    <p>30&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp21&nbsp&nbsp*&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp&nbsp&nbsp每晚的21:30</p>
                    <p>10&nbsp&nbsp&nbsp1&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp6,0&nbsp&nbsp&nbsp&nbsp每周六、周日的1:10</p>
                    <p>15&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp&nbsp&nbsp每小时的第15分钟执行</p>
                    <p>15&nbsp&nbsp&nbsp20&nbsp&nbsp*/2&nbsp*&nbsp&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp&nbsp&nbsp每隔两天的20点的第15分钟执行</p>
                    <p>45&nbsp&nbsp&nbsp4&nbsp&nbsp1,10&nbsp*&nbsp&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp&nbsp&nbsp每月1、10日的4:45</p>
                    <p>0&nbsp&nbsp&nbsp&nbsp23&nbsp&nbsp*&nbsp&nbsp&nbsp*&nbsp&nbsp&nbsp&nbsp6&nbsp&nbsp&nbsp&nbsp&nbsp每星期六的晚上11:00</p>

                    <p><b>如有疑问，咨询qq 2039399031</b></p>
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
