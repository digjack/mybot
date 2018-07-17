<template>
    <section>
        <!--工具条-->
        <el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
            <el-form :inline="true" :model="filters">
                <el-form-item>
                    <el-input v-model="keyword" placeholder="群名"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" v-on:click="getSources">查询</el-button>
                </el-form-item>
            </el-form>
        </el-col>

        <!--列表-->
        <el-table :data="groups" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
            <el-table-column type="UserName" width="60">
            </el-table-column>
            <el-table-column prop="NickName" label="群昵称" width="300" sortable>
            </el-table-column>
            <el-table-column prop="MemberCount" label="会员数" width="100"  sortable>
            </el-table-column>
            <el-table-column label="操作" width="350">
                <template scope="scope">
                    <el-button type="danger" size="small"  @click="handleList(scope.row)">查看会员</el-button>
                    <el-button type="danger" size="small" @click="msgPopup(scope.row)">发送消息</el-button>
                    <el-popover
                            placement="right"
                            width="300"
                            trigger="click">
                        <div>
                            <el-select v-model="selectedGroupId" placeholder="请选择">
                                <el-option
                                        v-for="item in label_list"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                            <el-button type="primary" size="small" @click="addUserToGroup(scope.row, selectedGroupId)">确定</el-button>
                        </div>
                        <el-button type="danger" size="small" slot="reference">标记群组</el-button>
                    </el-popover>
                </template>
            </el-table-column>
        </el-table>

        <!--工具条-->
        <el-col :span="24" class="toolbar">
            <el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="20" :total="total" style="float:right;">
            </el-pagination>
        </el-col>

        <el-dialog :title="currentUser.NickName" :visible.sync="msgDialog" :close-on-click-modal="false">
            <el-form  label-width="80px">
                <el-form-item label="">
                    <el-input v-model="msg" type="textarea" :autosize="{ minRows: 5, maxRows: 100}" placeholder="请输入消息" ></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="sendMsg(currentUser, msg)">发送</el-button>
                <el-button @click.native="msgDialog = false">关闭</el-button>
            </div>
        </el-dialog>

        <!--//成员列表-->
        <el-dialog title="成员列表" :visible.sync="groupDialogVisible"  :close-on-click-modal="false" custom-class="dialog-center">
            <el-table
                    :data="groupUsers"
                    height="250">
                <el-table-column
                        prop="NickName"
                        label="昵称">
                </el-table-column>
                <div slot="footer" class="dialog-footer">
                    <!--<el-button @click.native="sendMsg(currentUser, msg)">发送</el-button>-->
                    <el-button @click.native="groupDialogVisible = false">关闭</el-button>
                </div>
                <!--<el-table-column label="操作">-->
                    <!--<template slot-scope="scope">-->
                        <!--<el-button-->
                                <!--size="mini"-->
                                <!--type="danger"-->
                                <!--@click="handleDeleteUser(scope.$index, scope.row)">备注</el-button>-->
                    <!--</template>-->
                <!--</el-table-column>-->
            </el-table>
        </el-dialog>

    </section>
</template>

<script>
    import util from '../../common/js/util'
    import { Message } from 'element-ui';
    import { getGroups, addMember,getLabelList,sendMsg,listGroupMember } from '../../api/api';

    export default {
        data() {
            return {
                keyword:'',
                groups : [],
                msg:'',
                msgDialog:false,
                label_list: [],
                selectedGroupId: 0,
                listLoading :false,
                currentUser:{},
                groupDialogVisible: false,
                groupUsers: []
            }
        },
        methods: {
            init: function() {
                let vm = this;
                let param = {
                    keyword : this.keyword
                };
                vm.listLoading = true;
                getGroups(param).then((res) => {
                    vm.groups = res.data.groups;
                    vm.listLoading = false;
                });
            },
            msgPopup: function (user) {
                this.currentUser = user;
                this.msgDialog = true;
            },

            sendMsg :function (group) {
                let para = {
                    username: group.UserName,
                    content: this.msg
                };
                sendMsg(para).then((res) =>{
                    Message({
                        message : '发送成功'
                    });
                    this.currentUser = {};
                    this.msg = '';
                    this.msgDialog = false;
                });
            },
            addUserToGroup: function (row, label_id) {
                let para = {
                    member: [row],
                    label_id: label_id
                };
                addMember(para).then((res) =>  {
                    Message({
                        message : '加入成功'
                    });
                })
            },
            listLabel: function(){
                let para = {
                    name: '',
                    type: [3]
                };
                this.listLoading = true;
                getLabelList(para).then((res) => {
                    this.total = res.data.total;
                    this.label_list = res.data.list;
                    this.selectedGroupId = res.data.list[0].id;
                    this.listLoading = false;
                });
            },
            handleList: function (row) {
                let para = {
                    user_name : row.UserName
                };
                let vm = this;
                listGroupMember(para).then((res) => {
                    console.log(res);
                    vm.groupDialogVisible = true;
                    vm.groupUsers = res.members;
                });
            },
        },
        mounted() {
            this.init();
            this.listLabel();
        }
    }

</script>

<style>

</style>
