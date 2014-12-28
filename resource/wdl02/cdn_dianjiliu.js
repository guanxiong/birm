(function(){
 	APC_g_rate=69.01;                   
	var APC_gl_url = "http://jqmtcs.qq.com/rtv.png?plf=3";
    var APC_r_url = "http://jqmtcs.qq.com/rpt.png?plf=3&";
	APC_count = 0, APC_idx = [], APC_task = [];
	
	function APC_g_l(){
	    var s = document.createElement("script");
	    s.id="APC_cgi_ist"; 
	    document.getElementsByTagName("head")[0].appendChild(s);
	    s.src=APC_gl_url;
	}
	function apc_CallBack(data){
		var idx=0;
	    for(var i in data){
			if(i=="rCount"){
				APC_count = data[i];
			}else{
				if(!i.match(/\D/g)){
					APC_idx[idx]=i;
					APC_task[i]=data[i];
					idx++;
				}
			}
	    }
		APC_count=idx;
		APC_r_url+="cnt="+APC_count;
		APC_st(0,0);
		return;
	}
	function APC_st(i,t){
		var p=new Image();
		p.idx=i;
		p.st=new Date();
		p.t=t;
		p.onload=function(){p.onload=null;APC_r_ok(this.idx,this.st,this.t)};
		p.onerror=function(){p.onerror=null;APC_r_err(this.idx,this.st,this.t)}; 
		p.src=APC_task[APC_idx[i]]+"?"+Math.random();
	}
	function APC_r_ok(i,st,t){
		var data=new Date(), tm=data.getTime()-st.getTime();
		APC_r_url+="&r"+i+"="+APC_idx[i]+","+tm+",0";
		if(i<APC_count-1)
			APC_st(i+1,0);
		else{
			APC_Rpt(APC_r_url);
		}
	}
	function APC_r_err(i,st,t){
		var data=new Date();
		var tm=data.getTime()-st.getTime();
		APC_r_url+="&r"+i+"="+APC_idx[i]+","+tm+",1";
		if(i<APC_count-1)
			APC_st(i+1,0);
		else
		{
			APC_Rpt(APC_r_url);
		}
	}
	function APC_Rpt(s){
		var p = new Image();
		p.src=s;
	}
	
	try{
		window['apc_CallBack'] = apc_CallBack;
		var APC_rand=Math.floor(Math.random()*1000);
		if(APC_rand<APC_g_rate){
			APC_g_l();
		}
	}catch(e){}
})();/*  |xGv00|1b93d82102415300356fe26baea50f73 */
