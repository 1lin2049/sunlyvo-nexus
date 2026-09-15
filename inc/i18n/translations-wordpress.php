<?php
/**
 * WordPress 核心中文化翻译表
 *
 * 覆盖 WordPress 核心（default 域）的常用英文串到中文。
 *
 * @package SunLyvo_Nexus
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return [

    // =========================================================================
    // 文本域：default（WP 核心）
    // =========================================================================
    'default' => [

        // ---------------------------------------------------------------------
        // 后台菜单
        // ---------------------------------------------------------------------
        'Dashboard'                         => '仪表盘',
        'Posts'                             => '文章',
        'Media'                             => '媒体',
        'Pages'                             => '页面',
        'Comments'                          => '评论',
        'Appearance'                        => '外观',
        'Plugins'                           => '插件',
        'Users'                             => '用户',
        'Tools'                             => '工具',
        'Settings'                          => '设置',
        'Themes'                            => '主题',
        'Customize'                         => '自定义',
        'Widgets'                           => '小工具',
        'Menus'                             => '菜单',
        'Editor'                            => '编辑器',
        'General'                           => '常规',
        'Writing'                           => '撰写',
        'Reading'                           => '阅读',
        'Discussion'                        => '讨论',
        'Permalinks'                        => '固定链接',
        'Privacy'                           => '隐私',

        // ---------------------------------------------------------------------
        // 文章编辑
        // ---------------------------------------------------------------------
        'Add New'                           => '新建',
        'Add New Post'                      => '新建文章',
        'Add New Page'                      => '新建页面',
        'Edit'                              => '编辑',
        'Edit Post'                         => '编辑文章',
        'Edit Page'                         => '编辑页面',
        'Update'                            => '更新',
        'Publish'                           => '发布',
        'Save Draft'                        => '保存草稿',
        'Preview'                           => '预览',
        'Move to Trash'                     => '移入回收站',
        'Restore'                           => '恢复',
        'Delete Permanently'                => '永久删除',
        'View'                              => '查看',
        'View Post'                         => '查看文章',
        'View Page'                         => '查看页面',
        'Trash'                             => '回收站',
        'Draft'                             => '草稿',
        'Pending'                           => '待审',
        'Private'                           => '私有',
        'Published'                         => '已发布',
        'Scheduled'                         => '已计划',
        'Status'                            => '状态',

        // ---------------------------------------------------------------------
        // 编辑器字段
        // ---------------------------------------------------------------------
        'Title'                             => '标题',
        'Content'                           => '内容',
        'Excerpt'                           => '摘要',
        'Author'                            => '作者',
        'Categories'                        => '分类',
        'Tags'                              => '标签',
        'Featured image'                    => '特色图像',
        'Set featured image'                => '设置特色图像',
        'Remove featured image'             => '移除特色图像',
        'Permalink'                         => '固定链接',
        'Slug'                              => '别名',
        'Discussion'                        => '讨论',
        'Allow comments'                    => '允许评论',
        'Allow trackbacks and pingbacks on this page.' => '允许对这篇文章进行引用通告和通告。',
        'Revisions'                         => '修订版本',
        'Publish'                           => '发布',
        'Publish immediately'               => '立即发布',
        'Schedule'                          => '定时',

        // ---------------------------------------------------------------------
        // 列表页
        // ---------------------------------------------------------------------
        'Search Posts'                      => '搜索文章',
        'Search Pages'                      => '搜索页面',
        'Search Media'                      => '搜索媒体',
        'Search Users'                      => '搜索用户',
        'Filter'                            => '筛选',
        'Bulk actions'                      => '批量操作',
        'Apply'                             => '应用',
        'Select All'                        => '全选',
        'Date'                              => '日期',
        'Name'                              => '名称',
        'Email'                             => '邮箱',
        'Role'                              => '角色',
        'Posts'                             => '文章数',
        'All'                               => '全部',
        'Mine'                              => '我的',
        'Published'                         => '已发布',
        'Drafts'                            => '草稿',
        'Pending'                           => '待审',
        'Trash'                             => '回收站',

        // ---------------------------------------------------------------------
        // 用户
        // ---------------------------------------------------------------------
        'Username'                          => '用户名',
        'Password'                          => '密码',
        'Email'                             => '邮箱',
        'First Name'                        => '名字',
        'Last Name'                         => '姓氏',
        'Nickname'                          => '昵称',
        'Display name publicly as'          => '公开显示为',
        'Biographical Info'                 => '个人简介',
        'Website'                           => '网站',
        'Profile'                           => '个人资料',
        'Log Out'                           => '退出登录',
        'Log In'                            => '登录',
        'Register'                          => '注册',
        'Lost your password?'               => '忘记密码？',
        'Remember Me'                       => '记住我',
        'Roles'                             => '角色',
        'Administrator'                     => '管理员',
        'Editor'                            => '编辑',
        'Author'                            => '作者',
        'Contributor'                       => '投稿者',
        'Subscriber'                        => '订阅者',

        // ---------------------------------------------------------------------
        // 常见按钮与提示
        // ---------------------------------------------------------------------
        'Save Changes'                      => '保存更改',
        'Cancel'                            => '取消',
        'OK'                                => '确定',
        'Close'                             => '关闭',
        'Submit'                            => '提交',
        'Search'                            => '搜索',
        'Reset'                             => '重置',
        'Back'                              => '返回',
        'Next'                              => '下一步',
        'Previous'                          => '上一步',
        'Yes'                               => '是',
        'No'                                => '否',
        'Loading…'                          => '加载中…',
        'Please wait…'                      => '请稍候…',
        'Processing…'                       => '处理中…',
        'Are you sure?'                     => '确定？',
        'Something went wrong.'             => '出错了。',
        'Settings saved.'                   => '设置已保存。',
        'Changes saved.'                    => '更改已保存。',
        'Post updated.'                     => '文章已更新。',
        'Post published.'                   => '文章已发布。',
        'Page updated.'                     => '页面已更新。',
        'Page published.'                   => '页面已发布。',

        // ---------------------------------------------------------------------
        // 无障碍
        // ---------------------------------------------------------------------
        'Skip to content'                   => '跳转到内容',
        'Skip to main content'              => '跳转到主内容',
        'Menu'                              => '菜单',
        'Close menu'                        => '关闭菜单',
        'Open menu'                         => '打开菜单',

        // ---------------------------------------------------------------------
        // 搜索结果与归档
        // ---------------------------------------------------------------------
        'Search Results for: %s'            => '搜索结果：%s',
        'Nothing Found'                     => '没有找到内容',
        'Ready to publish your first post? <a href="%1$s">Get started here</a>.' => '准备发布第一篇文章？<a href="%1$s">从这里开始</a>。',
        'Sorry, but nothing matched your search terms. Please try again with some different keywords.' => '抱歉，没有找到符合搜索条件的内容。请换些关键词再试。',
        'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.' => '似乎没有找到你想要的内容。试试搜索。',

        // ---------------------------------------------------------------------
        // 分页
        // ---------------------------------------------------------------------
        'Previous page'                     => '上一页',
        'Next page'                         => '下一页',
        'Previous'                          => '上一页',
        'Next'                              => '下一页',
        'Older posts'                       => '更早的文章',
        'Newer posts'                       => '更新的文章',
        'Posts navigation'                  => '文章导航',
        'Page %1$s of %2$s'                 => '第 %1$s 页，共 %2$s 页',

        // ---------------------------------------------------------------------
        // 评论
        // ---------------------------------------------------------------------
        'Leave a Reply'                     => '发表评论',
        'Leave a Reply to %s'               => '回复 %s',
        'Post Comment'                      => '发表评论',
        'Comment'                           => '评论',
        'Comments'                          => '评论',
        'Your email address will not be published.' => '邮箱地址不会被公开。',
        'Required fields are marked %s'     => '必填项已用 %s 标记',
        'Comment navigation'                => '评论导航',
        'Older Comments'                    => '较早的评论',
        'Newer Comments'                    => '较新的评论',
        'Comments are closed.'              => '评论已关闭。',
    ],
];