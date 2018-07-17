<template>
    <section>
        <!--工具条-->
        <el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
            <el-form :inline="true" :model="filters">
                <el-form-item>
                    <el-input v-model="filters.name" placeholder="昵称"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleCurrentChange(1)">查询</el-button>
                </el-form-item>
            </el-form>
        </el-col>

        <!--列表-->
        <el-table :data="msg_list" highlight-current-row v-loading="listLoading"  style="width: 100%;">
            <el-table-column type="index" width="60">
            </el-table-column>
            <el-table-column prop="send_time" label="发送时间" width="150" sortable>
            </el-table-column>
            <el-table-column prop="to_nick" label="昵称" width="150" sortable>
            </el-table-column>
            <el-table-column prop="to_remarkname" label="备注" width="150" sortable>
            </el-table-column>
            <el-table-column prop="type" :formatter="formatMsgType"  label="类型" width="120" sortable>
            </el-table-column>
            <el-table-column prop="status" :formatter="formatMsgStatus" label="状态" width="120" sortable>
            </el-table-column>
            <el-table-column prop="content" label="内容" width="400"  sortable>
            </el-table-column>
        </el-table>

        <!--工具条-->
        <el-col :span="24" class="toolbar">
            <el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="size" :total="total" style="float:right;">
            </el-pagination>
        </el-col>

    </section>
</template>

<script>
    import util from '../../common/js/util'
    import { Message } from 'element-ui';
    import { listMsg } from '../../api/api';

    export default {
        data() {
            return {
                filters: {
                    name: ''
                },
                users: [],
                msg_list:[],
                total: 0,
                page: 1,
                size: 20,
                listLoading: false
            }
        },
        methods: {
            handleCurrentChange(val) {
                this.page = val;
                this.listMsg();
            },
            //获取任务列表
            listMsg() {
                let para = {
                    name: this.filters.name,
                    page:this.page,
                    size:this.size
                };
                this.listLoading = true;
                listMsg(para).then((res) => {
                    this.total = res.total;
                    this.msg_list = res.list;
                    this.listLoading = false;
                });
            },
            formatMsgType: function(row, column, cellValue){
                if(cellValue === 0 || cellValue === '0'){
                    return '手动发送';
                }
                if(cellValue === 1 || cellValue === '1'){
                    return '任务发送';
                }
                if(cellValue === 2 || cellValue === '2'){
                    return '计划发送';
                }
                if(cellValue === 3 || cellValue === '3'){
                    return '系统发送';
                }
                return cellValue;
            },
            formatMsgStatus: function(row, column, cellValue, index){
                if(cellValue === 0 || cellValue === '0'){
                    return '等待发送';
                }
                if(cellValue === 2 || cellValue === '2'){
                    return '发送成功';
                }
                if(cellValue === 4 || cellValue === '4'){
                    return '发送失败';
                }
                return cellValue;
            }
        },
        mounted() {
            this.listMsg();
        }
    }

</script>

<style>

</style>
