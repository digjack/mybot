<template>
    <section>
        <!--工具条-->
        <el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
            <el-form :inline="true" :model="filters">
                <!--<el-form-item>-->
                    <!--<el-input v-model="filters.name" placeholder="文件名"></el-input>-->
                <!--</el-form-item>-->
                <el-form-item>
                    <el-button type="primary" @click="handleAdd">新建计划</el-button>
                </el-form-item>
                <el-popover
                        placement="top-start"
                        title="注意事项"
                        width="600"
                        trigger="hover">
                    <p>1. 分组标签只在本网站内有效，没有与微信的标签功能同步</p>
                    <!--<p>2. 支持定时发送</p>-->
                    <p>2. 如有疑问，咨询qq 2039399031</p>
                    <i slot="reference" class="el-icon-question"></i>
                </el-popover>

            </el-form>
        </el-col>

        <!--列表-->
        <el-table :data="plan_list" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
            <el-table-column type="index" width="60">
            </el-table-column>
            <el-table-column prop="name" label="计划名" width="150" sortable>
            </el-table-column>
            <el-table-column prop="send_time" label="发送时间" width="150" sortable>
            </el-table-column>
            <el-table-column prop="count" label="发送数" width="120" sortable>
            </el-table-column>
            <el-table-column prop="success_count" label="成功数" width="120" sortable>
            </el-table-column>
            <el-table-column prop="type" label="类型" width="120" sortable>
            </el-table-column>
            <el-table-column prop="params" label="参数" :formatter="formatParams" width="120" sortable>
            </el-table-column>
            <el-table-column prop="content" label="内容" width="200" sortable>
            </el-table-column>
            <el-table-column prop="status" :formatter="formatStatus" label="状态" width="100" sortable>

            </el-table-column>
            <el-table-column label="操作" width="270">
                <template scope="scope">
                    <el-button type="danger" v-if="scope.row.status == 0" size="small"  @click="cancelPlan(scope.row)">取消</el-button>
                    <el-button type="danger" v-if="scope.row.status == 0" size="small"  @click="confirmPlan(scope.row)" :loading="send_status">确认发送</el-button>
                </template>
            </el-table-column>
        </el-table>

        <!--工具条-->
        <el-col :span="24" class="toolbar">
            <el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="20" :total="total" style="float:right;">
            </el-pagination>
        </el-col>

        <!--新建界面-->
        <el-dialog title="新建计划" :visible.sync="addFormVisible" :close-on-click-modal="false" custom-class="dialog-center">
            <el-form :model="addForm" label-width="80px" ref="addForm">
                <el-form-item label="计划名" prop="name">
                    <el-input v-model="addForm.name"></el-input>
                </el-form-item>
                <!--<el-form-item label="发送时间">-->
                    <!--<el-input id='send_time' v-model="addForm.send_time"></el-input>-->
                <!--</el-form-item>-->
                <el-form-item label="性别">
                    <el-radio-group  v-model="addForm.params.sex">
                        <el-radio label="all" >不限</el-radio>
                        <el-radio label="male" >男</el-radio>
                        <el-radio label="female" >女</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="省份">
                    <el-select v-model="addForm.params.province" multiple placeholder="不限">
                        <el-option v-for="prov in province_list" :label="prov" :value="prov"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item  label="本地标签">
                    <el-select v-model="addForm.params.local_label" clearable  placeholder="不限">
                        <el-option v-for="group in label_list" :label="group.name" :value="group.id"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="发送内容">
                    <el-input type="textarea" v-model="addForm.content" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="预计人数">
                    <span type="success" >{{expect_member_num}}</span>
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
    import { listPlan,addPlan,delPlan,countMember,confirmPlan,getProvince, getLabelList } from '../../api/api';

    export default {
        data() {
            return {
                descCycle: false,
                filters: {
                    name: ''
                },
                users: [],
                plan_list:[],
                total: 0,
                page: 1,
                listLoading: false,
                sels: [],//列表选中列
                label_list: [
                    {id: 1, name: '牛逼的分组'},
                    {id: 2, name: '小小的分组'}
                ],
                province_list: ['北京','厦门'],
                addFormVisible: false,//新增界面是否显示
                addLoading: false,
                addFormRules: {
//                    name: [
//                        { required: true, message: '请输入姓名', trigger: 'blur' }
//                    ]
                },
                //新增界面数据
                addForm: {
                    name: '',
                    params: {
                        'local_label': '',
                        'sex':'all',
                        'province': ''
                    },
                    content: ''
                },
                currentRow:{},
                isAdmin:false,
                showDetail: false,
                payButtonText:'确定',
                expect_member_num:0,
                send_loading: false
            }
        },
        methods: {
            handleCurrentChange(val) {
                this.page = val;
                this.getPlan();
            },
            //获取任务列表
            getPlan() {
                let para = {
                    name: this.filters.name,
                };
                this.listLoading = true;
                listPlan(para).then((res) => {
                    this.total = res.total;
                    this.plan_list = res.list;
                    this.listLoading = false;
                });
            },
            //删除
            cancelPlan: function (row) {
                this.$confirm('确认是否取消该计划?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    //NProgress.start();
                    let para = { id: row.id };
                    delPlan(para).then((res) => {
                        this.listLoading = false;
                        //NProgress.done();
                        this.$message({
                            message: '取消成功',
                            type: 'success'
                        });
                        this.getPlan();
                    });
                }).catch(() => {

                });
            },

            //显示新增界面
            handleAdd: function () {
                this.addFormVisible = true;
                this.initAddForm();
            },
            initAddForm: function () {
                this.addForm = {
                    name: '',
                    params: {
                        'local_label': '',
                        'sex':'all',
                        'province': ''
                    },
                    content: ''
                }
            },
            //新增
            addSubmit: function () {
                this.$refs.addForm.validate((valid) => {
                    if (valid) {
                        this.$confirm('确认提交吗？', '提示', {}).then(() => {
                            this.addLoading = true;
                            //NProgress.start();
                            let para = Object.assign({}, this.addForm);
                            addPlan(para).then((res) => {
                                this.addLoading = false;
                                //NProgress.done();
                                this.$message({
                                    message: '提交成功，请在列表页确认发送。',
                                    type: 'success'
                                });
                                this.$refs['addForm'].resetFields();
                                this.addFormVisible = false;
                                this.getPlan();
                            });
                        });
                    }
                });
            },

            selsChange: function (sels) {
                this.sels = sels;
            },

            confirmPlan: function (row) {
                this.send_loading = true;
                //NProgress.start();
                let para = { id: row.id };
                confirmPlan(para).then((res) => {
                    this.send_loading = false;
                    //NProgress.done();
                    this.$message({
                        message: '发送完成',
                        type: 'success'
                    });
                    this.getPlan();
                }).catch((error) => {
                    this.send_loading = false;
                    this.$message({
                        message: '发送失败',
                        type: 'fail'
                    });
                    this.getPlan();
                });
            },
            listLabels: function(str){
                let para = {
                    type:[1,2,3]
                };
                getLabelList(para).then((res) => {
                    this.label_list = res.data.list;
                });
            },
            formatReceiver: function(row, column, cellValue, index){
                if(row['receiver_type'] === 'group'){
                    return row['group_name'];
                }
                return row['receiver'];
            },
            formatStatus:function(row, column, cellValue, index){
                if(cellValue === 2 || cellValue === '2'){
                    return '完成';
                }
                if(cellValue === 1 || cellValue === '1'){
                    return '发送中';
                }
                if(cellValue === 4 || cellValue === '4'){
                    return '已取消';
                }
                if(cellValue === 3 || cellValue === '3'){
                    return '失败';
                }
                return '等待';
            },
            formatParams: function (row, column, cellValue, index) {
                return JSON.stringify(cellValue);
            },
            listProvince: function () {
                getProvince().then((res) => {
                    this.province_list = res;
                });
                console.log(this.province_list);
            },
            countNum: function () {
                let param = this.addForm.params;
                countMember(param).then((res) =>{
                    this.expect_member_num = res.count;
                });
            }
        },
        mounted() {
            this.getPlan();
            this.listLabels();
            this.listProvince();
            this.initAddForm();
        }
    }

</script>

<style>

</style>
