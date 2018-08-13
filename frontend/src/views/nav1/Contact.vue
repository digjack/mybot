<template>
    <section>
        <!--工具条-->
        <el-col :span="24" class="toolbar" style="padding-bottom: 0px;">
            <el-form :inline="true" :model="filters">
                <el-form-item>
                    <el-input v-model="keyword" placeholder="昵称"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" v-on:click="search">查询</el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" v-on:click="exportContacts">好友导出</el-button>
                </el-form-item>
                <el-form-item>
                <el-popover
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
                        <el-button type="primary" size="small" @click="batchAddToGroup(selectedGroupId)">确定</el-button>
                    </div>
                    <el-button type="primary" slot="reference">批量标识</el-button>
                </el-popover>
                </el-form-item>
            </el-form>
        </el-col>

        <!--列表-->
        <el-table :data="contacts" highlight-current-row v-loading="listLoading" @selection-change="selsChange" style="width: 100%;">
            <el-table-column
                    type="selection"
                    width="55">
            </el-table-column>
            <el-table-column type="index" width="50">
            </el-table-column>
            <el-table-column prop="NickName" label="昵称" width="150" sortable>
            </el-table-column>
            <el-table-column prop="RemarkName" label="备注" width="150" sortable>
            </el-table-column>
            <el-table-column prop="Sex" label="性别" width="100"  sortable>
            </el-table-column>
            <el-table-column prop="City" label="城市" width="100"  sortable>
            </el-table-column>
            <el-table-column prop="Signature" label="心情" width="400" sortable>
            </el-table-column>
             <el-table-column label="操作" width="200">
                <template scope="scope">
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
                            <el-button type="primary" size="small" @click="addUserTogroup(scope.row, selectedGroupId )">确定</el-button>
                        </div>
                        <el-button type="danger" size="small" slot="reference">标记好友</el-button>
                    </el-popover>
                </template>
            </el-table-column>
        </el-table>

        <!--工具条-->
        <el-col :span="24" class="toolbar">
            <!--<el-button v-if="false" type="danger" @click="batchRemove" :disabled="this.sels.length===0 || true">批量删除</el-button>-->
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

    </section>
</template>

<script>
    import util from '../../common/js/util'
    import { Message } from 'element-ui';
    import { getContact, sendMsg, getLabelList, addMember } from '../../api/api';

    export default {
        data() {
            return {
                groupTipVisible:false,
                keyword:'',
                contacts : [],
                listLoading: false,
                msgDialog:false,
                currentUser:{},
                msg:'',
                selectedGroupId:1,
                label_list: [],
                sels: '',
                page: 1,
                size: 20,
                total:0
            }
        },
        methods: {
            init: function() {
                let vm = this;
                let param = {
                    keyword : this.keyword,
                    page : this.page,
                    size : this.size
                };
                vm.listLoading = true;
                getContact(param).then((res) => {
                    vm.contacts = res.data.friends;
                    vm.total = res.data.total;
                    vm.listLoading = false;
                });
            },
            search: function () {
                this.page = 1;
                this.init();
            },
            handleCurrentChange(val) {
                this.page = val;
                this.init();
            },
            msgPopup: function (user) {
                this.currentUser = user;
                this.msgDialog = true;
            },
            sendMsg :function (user, msg) {
                let para = {
                    username: user.UserName,
                    content: msg
                };
                sendMsg(para).then((res) =>{
                    Message({
                        message : '发送成功',
                        type: 'success'
                    });
                    this.currentUser = {};
                    this.msg = '';
                    this.msgDialog = false;
                });
            },
            listLabel: function(){
                let para = {
                    name: '',
                    type: [1, 2]
                };
                this.listLoading = true;
                getLabelList(para).then((res) => {
                    this.total = res.data.total;
                    this.label_list = res.data.list;
                    this.selectedGroupId = res.data.list[0].id;
                    this.listLoading = false;
                });
            },
            addUserTogroup: function (row, groupId) {
                let para = {
                    member: [row],
                    nick_name: row.NickName,
                    remark_name : row.Remarkname,
                    label_id: groupId
                }
                addMember(para).then((res) =>  {
                    Message({
                        message : '加入成功',
                        type: 'success'
                    });
                })
                
            },
            selsChange: function (sels) {
                this.sels = sels;
            },
            //批量删除
            batchAddToGroup: function (groupId) {
//                var nick_names = this.sels.map(item => item.NickName).toString();
//                var remark_names = this.sels.map(item => item.RemarkName).toString();
//                this.listLoading = true;
                //NProgress.start();
                if(! this.sels[0]){
                    Message({
                        message : '请先勾选会员'
                    });
                }else{
                    let para = {
                        member: this.sels,
                        label_id: groupId
                    };
                    addMember(para).then((res) => {
                        Message({
                            message : res.add_count +'个会员加入成功'
                        });
                    });
                }
            },
            exportContacts:function () {
                var win = window.open('/api/contact/export', '_blank');
                win.focus();
            }
        },
        mounted() {
            this.init();
            this.listLabel();
        }
    }

</script>

<style>

</style>
