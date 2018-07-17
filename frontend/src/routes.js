import Login from './views/Login.vue'
import NotFound from './views/404.vue'
import Home from './views/Home.vue'
import Main from './views/Main.vue'
import Info from './views/nav1/Info.vue'

import Contact from './views/nav1/Contact.vue'
import Group from './views/nav1/Group.vue'
import Label from './views/nav2/Label.vue'
import Send from './views/nav2/Send.vue'
import Task from './views/nav2/Task.vue'

let routes = [
    {
        path: '/login',
        component: Login,
        name: '',
        hidden: true
    },
    {
        path: '/404',
        component: NotFound,
        name: '',
        hidden: true
    },
    //{ path: '/main', component: Main },
    {
        path: '/',
        component: Home,
        name: '账号信息',
        iconCls: 'el-icon-message',//图标样式class
        children: [
            { path: '/main', component: Main, name: '主页', hidden: true },
            { path: '/info', component: Info, name: '状态' },
            { path: '/contact', component: Contact, name: '联系人' },
            { path: '/group', component: Group, name: '群组' },
        ]
    },
    {
        path: '/',
        component: Home,
        name: '设置',
        iconCls: 'fa fa-id-card-o',
        children: [
            { path: '/task', component: Task, name: '定时任务' },
            // { path: '/send', component: Send, name: '群发任务' },
            { path: '/label', component: Label, name: '本地标签' }
        ]
    },
    {
        path: '/',
        component: Home,
        name: '日志',
        iconCls: 'fa fa-id-card-o',
        children: [
            // { path: '/log/task', component: Send, name: '消息记录' }
            { path: '/send', component: Send, name: '消息记录' }
        ]
    },
    {
        path: '*',
        hidden: true,
        redirect: { path: '/404' }
    }
];

export default routes;