function hashSubmit(){
			var form = document.forms.form;
			var username = document.getElementsByName('username')[0];
			var password = document.getElementsByName('password')[0];
			var hash = sha256(username.value + password.value);
			document.getElementsByName('hash')[0].value = hash;
			username.value = "";
			password.value = "";
			document.cookie = createSessionCookie();
			return true;
		}
		function sha256(str){
			var session = document.getElementsByName('authSession')[0].value;
			return SHA256Hash(SHA256Hash(str)+session);
		}
		function createSessionCookie(){
			var session = document.getElementsByName('authSession')[0].value;
			var cookie = 'session=' + session + '; path=/; max-age=86400;';
			return cookie;
		}
		SHA256=new function(){
		var k=[
		0x428a2f98,0x71374491,0xb5c0fbcf,0xe9b5dba5,0x3956c25b,0x59f111f1,0x923f82a4,0xab1c5ed5,
		0xd807aa98,0x12835b01,0x243185be,0x550c7dc3,0x72be5d74,0x80deb1fe,0x9bdc06a7,0xc19bf174,
		0xe49b69c1,0xefbe4786,0x0fc19dc6,0x240ca1cc,0x2de92c6f,0x4a7484aa,0x5cb0a9dc,0x76f988da,
		0x983e5152,0xa831c66d,0xb00327c8,0xbf597fc7,0xc6e00bf3,0xd5a79147,0x06ca6351,0x14292967,
		0x27b70a85,0x2e1b2138,0x4d2c6dfc,0x53380d13,0x650a7354,0x766a0abb,0x81c2c92e,0x92722c85,
		0xa2bfe8a1,0xa81a664b,0xc24b8b70,0xc76c51a3,0xd192e819,0xd6990624,0xf40e3585,0x106aa070,
		0x19a4c116,0x1e376c08,0x2748774c,0x34b0bcb5,0x391c0cb3,0x4ed8aa4a,0x5b9cca4f,0x682e6ff3,
		0x748f82ee,0x78a5636f,0x84c87814,0x8cc70208,0x90befffa,0xa4506ceb,0xbef9a3f7,0xc67178f2];
		var w=32;
		var ROTR=function(v,n){return(v>>>n)|(v<<(w-n))};
		var Ch=function(x,y,z){return(x&y)^(~x&z)};
		var Maj=function(x,y,z){return(x&y)^(x&z)^(y&z)};
		var S0=function(v){return ROTR(v,2)^ROTR(v,13)^ROTR(v,22)};
		var S1=function(v){return ROTR(v,6)^ROTR(v,11)^ROTR(v,25)};
		var s0=function(v){return ROTR(v,7)^ROTR(v,18)^(v>>>3)};
		var s1=function(v){return ROTR(v,17)^ROTR(v,19)^(v>>>10)};
		var add2=function(v1,v2){return(v1+v2)&0xffffffff};
		var add4=function(v1,v2,v3,v4){return(v1+v2+v3+v4)&0xffffffff};
		var add5=function(v1,v2,v3,v4,v5){return(v1+v2+v3+v4+v5)&0xffffffff};
		var btoi=function(b,i){return(b[i++]<<24)+(b[i++]<<16)+(b[i++]<<8)+(b[i++])};
		var tobs=function(b,v){var i;for(i=3;i>=0;i--)b[b.length]=(v>>>(i*8))&0xff};
		var compute=function(hash,block){
		var t1,t2,i,j,w=[],h=[];
		for(i=0;i<8;i++)h[i]=hash[i];
		for(i=0;i<64;i++){
		if(i<16)w[i]=btoi(block,i<<2);else w[i]=add4(s1(w[i-2]),w[i-7],s0(w[i-15]),w[i-16]);
		t1=add5(h[7],S1(h[4]),Ch(h[4],h[5],h[6]),k[i],w[i]);
		t2=add2(S0(h[0]),Maj(h[0],h[1],h[2]));
		for(j=7;j>0;j--)h[j]=h[j-1];h[4]=add2(h[4],t1);h[0]=add2(t1,t2);}
		for(i=0;i<8;i++)h[i]=add2(h[i],hash[i]);
		return h;}

		this.ComputeHash=function(v){
		var blk,i,j,b=[],r=[];
		var ln=v.length<<3;
		var h=[0x6a09e667,0xbb67ae85,0x3c6ef372,0xa54ff53a,0x510e527f,0x9b05688c,0x1f83d9ab,0x5be0cd19];
		v[v.length]=0x80;
		blk=(v.length>>>6)+1;
		if((v.length&0x3f)>56)blk++;
		for(i=v.length;i<(blk<<6);i++)v[i]=0x00;
		for(i=0;i<4;i++)v[(blk<<6)-1-i]=(ln>>>(i*8))&0xff;
		for(i=0;i<blk;i++){
		for(j=0;j<64;j++)b[j]=v[(i<<6)+j];
		h=compute(h,b);}
		for(i=0;i<8;i++)tobs(r,h[i]);
		return r;}
		}

		function SHA256Hash(t){
		var btos=function(v){var i,r="";for(i=0;i<v.length;i++)r+=(v[i]>0x0f?"":"0")+v[i].toString(16);return r};
		var stob=function(v){var i,n,c,r=[];
		for(i=n=0;i<v.length;i++){c=v.charCodeAt(i);if(c<=0xff)r[n++]=c;else{r[n++]=c>>>8;r[n++]=c&0xff}};return r};
		return btos(SHA256.ComputeHash(stob(t)));
		};
