(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-basics-layout"],{1271:function(i,s,t){"use strict";t.r(s);var a=t("17a1"),e=t.n(a);for(var n in a)"default"!==n&&function(i){t.d(s,i,(function(){return a[i]}))}(n);s["default"]=e.a},"17a1":function(i,s,t){"use strict";Object.defineProperty(s,"__esModule",{value:!0}),s.default=void 0;var a={data:function(){return{CustomBar:this.CustomBar,TabCur:0,avatar:["https://ossweb-img.qq.com/images/lol/web201310/skin/big10001.jpg","https://ossweb-img.qq.com/images/lol/web201310/skin/big81005.jpg","https://ossweb-img.qq.com/images/lol/web201310/skin/big25002.jpg","https://ossweb-img.qq.com/images/lol/web201310/skin/big99008.jpg"],tabNav:["Flex布局","Grid布局","辅助布局"]}},methods:{tabSelect:function(i){this.TabCur=i.currentTarget.dataset.id,this.scrollLeft=60*(i.currentTarget.dataset.id-1)}}};s.default=a},"2daa":function(i,s,t){var a=t("fe1b");"string"===typeof a&&(a=[[i.i,a,""]]),a.locals&&(i.exports=a.locals);var e=t("4f06").default;e("0f07da18",a,!0,{sourceMap:!1,shadowMode:!1})},"4ece":function(i,s,t){"use strict";t.r(s);var a=t("7818"),e=t("1271");for(var n in e)"default"!==n&&function(i){t.d(s,i,(function(){return e[i]}))}(n);t("b84d");var v,d=t("f0c5"),l=Object(d["a"])(e["default"],a["b"],a["c"],!1,null,"75a70b24",null,!1,a["a"],v);s["default"]=l.exports},7818:function(i,s,t){"use strict";var a,e=function(){var i=this,s=i.$createElement,t=i._self._c||s;return t("v-uni-view",[t("cu-custom",{attrs:{bgColor:"bg-gradual-blue",isBack:!0}},[t("template",{attrs:{slot:"backText"},slot:"backText"},[i._v("返回")]),t("template",{attrs:{slot:"content"},slot:"content"},[i._v("布局")])],2),t("v-uni-scroll-view",{staticClass:"bg-white nav text-center fixed",style:[{top:i.CustomBar+"px"}],attrs:{"scroll-x":!0}},i._l(i.tabNav,(function(s,a){return t("v-uni-view",{key:a,staticClass:"cu-item",class:a==i.TabCur?"text-blue cur":"",attrs:{"data-id":a},on:{click:function(s){arguments[0]=s=i.$handleEvent(s),i.tabSelect.apply(void 0,arguments)}}},[i._v(i._s(i.tabNav[a]))])})),1),0==i.TabCur?[t("v-uni-view",{staticClass:"cu-bar bg-white solid-bottom margin-top"},[t("v-uni-view",{staticClass:"action"},[t("v-uni-text",{staticClass:"cuIcon-title text-blue"}),i._v("固定尺寸")],1)],1),t("v-uni-view",{staticClass:"padding bg-white"},[t("v-uni-view",{staticClass:"flex flex-wrap"},[t("v-uni-view",{staticClass:"basis-xs bg-grey margin-xs padding-sm radius"},[i._v("xs(20%)")]),t("v-uni-view",{staticClass:"basis-df"}),t("v-uni-view",{staticClass:"basis-sm bg-grey margin-xs padding-sm radius"},[i._v("sm(40%)")]),t("v-uni-view",{staticClass:"basis-df"}),t("v-uni-view",{staticClass:"basis-df bg-grey margin-xs padding-sm radius"},[i._v("sub(50%)")]),t("v-uni-view",{staticClass:"basis-lg bg-grey margin-xs padding-sm radius"},[i._v("lg(60%)")]),t("v-uni-view",{staticClass:"basis-xl bg-grey margin-xs padding-sm radius"},[i._v("xl(80%)")])],1)],1),t("v-uni-view",{staticClass:"cu-bar bg-white  margin-top solid-bottom"},[t("v-uni-view",{staticClass:"action"},[t("v-uni-text",{staticClass:"cuIcon-title text-blue"}),i._v("比例布局")],1)],1),t("v-uni-view",{staticClass:"padding bg-white"},[t("v-uni-view",{staticClass:"flex"},[t("v-uni-view",{staticClass:"flex-sub bg-grey padding-sm margin-xs radius"},[i._v("1")]),t("v-uni-view",{staticClass:"flex-sub bg-grey padding-sm margin-xs radius"},[i._v("1")])],1),t("v-uni-view",{staticClass:"flex  p-xs margin-bottom-sm mb-sm"},[t("v-uni-view",{staticClass:"flex-sub bg-grey padding-sm margin-xs radius"},[i._v("1")]),t("v-uni-view",{staticClass:"flex-twice bg-grey padding-sm margin-xs radius"},[i._v("2")])],1),t("v-uni-view",{staticClass:"flex  p-xs margin-bottom-sm mb-sm"},[t("v-uni-view",{staticClass:"flex-sub bg-grey padding-sm margin-xs radius"},[i._v("1")]),t("v-uni-view",{staticClass:"flex-twice bg-grey padding-sm margin-xs radius"},[i._v("2")]),t("v-uni-view",{staticClass:"flex-treble bg-grey padding-sm margin-xs radius"},[i._v("3")])],1)],1),t("v-uni-view",{staticClass:"cu-bar bg-white  margin-top solid-bottom"},[t("v-uni-view",{staticClass:"action"},[t("v-uni-text",{staticClass:"cuIcon-title text-blue"}),i._v("水平对齐(justify)")],1)],1),t("v-uni-view",{staticClass:"bg-white"},[t("v-uni-view",{staticClass:"flex solid-bottom padding justify-start"},[t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("start")]),t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("start")])],1),t("v-uni-view",{staticClass:"flex solid-bottom padding justify-end"},[t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("end")]),t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("end")])],1),t("v-uni-view",{staticClass:"flex solid-bottom padding justify-center"},[t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("center")]),t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("center")])],1),t("v-uni-view",{staticClass:"flex solid-bottom padding justify-between"},[t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("between")]),t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("between")])],1),t("v-uni-view",{staticClass:"flex solid-bottom padding justify-around"},[t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("around")]),t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("around")])],1)],1),t("v-uni-view",{staticClass:"cu-bar bg-white  margin-top solid-bottom"},[t("v-uni-view",{staticClass:"action"},[t("v-uni-text",{staticClass:"cuIcon-title text-blue"}),i._v("垂直对齐(align)")],1)],1),t("v-uni-view",{staticClass:"bg-white"},[t("v-uni-view",{staticClass:"flex solid-bottom padding align-start"},[t("v-uni-view",{staticClass:"bg-grey padding-lg margin-xs radius"},[i._v("ColorUi")]),t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("start")])],1),t("v-uni-view",{staticClass:"flex solid-bottom padding align-end"},[t("v-uni-view",{staticClass:"bg-grey padding-lg margin-xs radius"},[i._v("ColorUi")]),t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("end")])],1),t("v-uni-view",{staticClass:"flex solid-bottom padding align-center"},[t("v-uni-view",{staticClass:"bg-grey padding-lg margin-xs radius"},[i._v("ColorUi")]),t("v-uni-view",{staticClass:"bg-grey padding-sm margin-xs radius"},[i._v("center")])],1)],1)]:i._e(),1==i.TabCur?[t("v-uni-view",{staticClass:"cu-bar bg-white  margin-top solid-bottom"},[t("v-uni-view",{staticClass:"action"},[t("v-uni-text",{staticClass:"cuIcon-title text-blue"}),i._v("等分列")],1),t("v-uni-view",{staticClass:"action"})],1),t("v-uni-view",{staticClass:"bg-white padding"},i._l(5,(function(s,a){return t("v-uni-view",{key:a,staticClass:"grid margin-bottom text-center",class:"col-"+(a+1)},i._l(2*(a+1),(function(s,a){return t("v-uni-view",{key:a,staticClass:"padding",class:a%2==0?"bg-cyan":"bg-blue"},[i._v(i._s(a+1))])})),1)})),1),t("v-uni-view",{staticClass:"cu-bar bg-white  margin-top solid-bottom"},[t("v-uni-view",{staticClass:"action"},[t("v-uni-text",{staticClass:"cuIcon-title text-blue"}),i._v("等高")],1),t("v-uni-view",{staticClass:"action"})],1),t("v-uni-view",{staticClass:"bg-white padding"},[t("v-uni-view",{staticClass:"grid col-4 grid-square"},i._l(i.avatar,(function(s,a){return t("v-uni-view",{key:a,staticClass:"bg-img",style:[{backgroundImage:"url("+i.avatar[a]+")"}]})})),1)],1)]:i._e(),2==i.TabCur?[t("v-uni-view",{staticClass:"cu-bar bg-white margin-top solid-bottom"},[t("v-uni-view",{staticClass:"action"},[t("v-uni-text",{staticClass:"cuIcon-title text-blue"}),i._v("浮动")],1)],1),t("v-uni-view",{staticClass:"bg-white padding"},[t("v-uni-view",{staticClass:" cf padding-sm"},[t("v-uni-view",{staticClass:"bg-grey radius fl padding-sm"},[i._v("ColorUi fl")]),t("v-uni-view",{staticClass:"bg-grey radius fr padding-sm"},[i._v("ColorUi fr")])],1)],1),t("v-uni-view",{staticClass:"cu-bar bg-white  solid-bottom margin-top solid-bottom"},[t("v-uni-view",{staticClass:"action"},[t("v-uni-text",{staticClass:"cuIcon-title text-blue"}),i._v("内外边距")],1)],1),t("v-uni-view",{staticClass:"bg-white"},[t("v-uni-view",{staticClass:"padding bg-gray"},[i._v("{size}的尺寸有xs/sm/df/lg/xl")]),t("v-uni-view",{staticClass:"flex flex-wrap padding solid-top"},[t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("外边距")]),t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("内边距")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".margin-{size}")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".padding-{size}")])],1),t("v-uni-view",{staticClass:"flex flex-wrap padding solid-top"},[t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("水平方向外边距")]),t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("水平方向内边距")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".margin-lr-{size}")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".padding-lr-{size}")])],1),t("v-uni-view",{staticClass:"flex flex-wrap padding solid-top"},[t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("垂直方向外边距")]),t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("垂直方向内边距")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".margin-tb-{size}")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".padding-tb-{size}")])],1),t("v-uni-view",{staticClass:"flex flex-wrap padding solid-top"},[t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("上外边距")]),t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("上内边距")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".margin-top-{size}")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".padding-top-{size}")])],1),t("v-uni-view",{staticClass:"flex flex-wrap padding solid-top"},[t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("右外边距")]),t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("右内边距")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".margin-right-{size}")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".padding-right-{size}")])],1),t("v-uni-view",{staticClass:"flex flex-wrap padding solid-top"},[t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("下外边距")]),t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("下内边距")]),t("v-uni-view",{staticClass:"basis-df"},[i._v("margin-bottom-{size}")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".padding-bottom-{size}")])],1),t("v-uni-view",{staticClass:"flex flex-wrap padding solid-top"},[t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("左外边距")]),t("v-uni-view",{staticClass:"basis-df padding-bottom-xs"},[i._v("左内边距")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".margin-left-{size}")]),t("v-uni-view",{staticClass:"basis-df"},[i._v(".padding-left-{size}")])],1)],1)]:i._e()],2)},n=[];t.d(s,"b",(function(){return e})),t.d(s,"c",(function(){return n})),t.d(s,"a",(function(){return a}))},b84d:function(i,s,t){"use strict";var a=t("2daa"),e=t.n(a);e.a},fe1b:function(i,s,t){var a=t("24fb");s=a(!1),s.push([i.i,"uni-page-body[data-v-75a70b24]{padding-top:45px}",""]),i.exports=s}}]);