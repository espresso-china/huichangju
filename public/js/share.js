wx.ready(function () {
    //分享到朋友圈
    wx.onMenuShareTimeline({
        title: shareData.Title, // 分享标题
        desc: shareData.Content, // 分享描述
        link: shareData.sendFriendLink, // 分享链接
        imgUrl: shareData.imgUrl, // 分享图标
        success: function () {
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
    //分享给朋友
    wx.onMenuShareAppMessage({
        title: shareData.Title, // 分享标题
        desc: shareData.Content, // 分享描述
        link: shareData.sendFriendLink, // 分享链接
        imgUrl: shareData.imgUrl, // 分享图标

        success: function () {
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
    //分享到qq
    wx.onMenuShareQQ({
        title: shareData.Title, // 分享标题
        desc: shareData.Content, // 分享描述
        link: shareData.sendFriendLink, // 分享链接
        imgUrl: shareData.imgUrl, // 分享图标
        success: function () {
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
    //分享到微博
    wx.onMenuShareWeibo({
        title: shareData.Title, // 分享标题
        desc: shareData.Content, // 分享描述
        link: shareData.sendFriendLink, // 分享链接
        imgUrl: shareData.imgUrl, // 分享图标
        success: function () {
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
})
