import axios from 'axios';

let base = 'api';

export const requestLogin = params => { return axios.post(`${base}/login`, params).then(res => res.data); };
export const getUserListPage = params => { return axios.get(`${base}/user/listpage`, { params: params }); };
export const removeUser = params => { return axios.get(`${base}/user/remove`, { params: params }); };
export const batchRemoveUser = params => { return axios.get(`${base}/user/batchremove`, { params: params }); };
export const editUser = params => { return axios.get(`${base}/user/edit`, { params: params }); };
export const addUser = params => { return axios.get(`${base}/user/add`, { params: params }); };

// //订单接口
// export const ListOrderStatus = params => { return axios.get(`${base}/order/status`, { params: params }); };
// export const payOrder = params => { return axios.post(`${base}/order`, params).then(res => res.data); };
//
// export const getSourceList = params => { return axios.get(`${base}/resource/list`, { params: params }); };
// export const saveResource = params => { return axios.post(`${base}/resource`, params).then(res => res.data); };
// export const deleteResource = params => { return axios.post(`${base}/resource/delete`, params).then(res => res.data); };
//
// //工具箱-号码转化
// export const transfer = params => { return axios.post(`${base}/tool/vcf/mobile`, params).then(res => res.data); };
// export const qr = params =>  { return axios.get(`${base}/tool/vcf/qr`, { params: params }); };
// export const ListenOrderStatus = params => { return axios.get(`${base}/tool/vcf/status`, { params: params }); };


export const initVbot = params => { return axios.get(`${base}/vbot/info`, { params: params }); };
export const qr = params => { return axios.get(`${base}/vbot/qr`, { params: params }); };
export const getStatus = params => { return axios.get(`${base}/vbot/status`, { params: params }); };
export const getContact = params => { return axios.get(`${base}/vbot/contacts`, { params: params }); };
export const getGroups = params => { return axios.get(`${base}/vbot/groups`, { params: params }); };
export const sendMsg = params => { return axios.post(`${base}/vbot/send`, params ).then(res => res.data); };
export const testMsg = params => { return axios.get(`${base}/vbot/test`); };
export const clearAuth = params => { return axios.get(`${base}/vbot/clear`); };

export const getTaskList = params => { return axios.get(`${base}/task/list`,  { params: params }); };
export const saveTask = params => { return axios.post(`${base}/task`, params); };
export const delTask = params => { return axios.delete(`${base}/task/${params.id}`, params); };


export const getLabelList = params => { return axios.get(`${base}/label/list`,  { params: params }); };
export const saveLabel = params => { return axios.post(`${base}/label`, params); };
export const delLabel = params => { return axios.delete(`${base}/label/${params.id}`,  { params: params }); };

export const listMember = params => { return axios.get(`${base}/label/user/list`, { params: params }); };
export const addMember = params => { return axios.post(`${base}/label/user`, params).then(res => res.data); };
export const delMember = params => { return axios.delete(`${base}/label/user/${params.id}`, { params: params }).then(res => res.data); };

export const listGroupMember = params => { return axios.get(`${base}/group/user/list`, { params: params }).then(res => res.data); };

export const listMsg = params => { return axios.get(`${base}/msg/list`, { params: params }).then(res => res.data); };

//群发计划

export const listPlan = params => { return axios.get(`${base}/plan`, { params: params }).then(res => res.data); };
export const addPlan = params => { return axios.post(`${base}/plan`, params).then(res => res.data); };
export const delPlan = params => { return axios.delete(`${base}/plan/${params.id}`, { params: params }).then(res => res.data); };
export const countMember = params => { return axios.post(`${base}/plan/count`, { params: params }).then(res => res.data); };
export const confirmPlan = params => { return axios.post(`${base}/plan/confirm`, params ).then(res => res.data); };
export const getProvince = params => { return axios.get(`${base}/plan/province`, { params: params }).then(res => res.data); };



