var SmartStars=/*286329323030372053204368616C6D657273*/
{
  /*** Download with instructions from: http://scripterlative.com?smartstars ***/   
   
 data:[], logged:0,
   
 init:function( ratingId, formElem, rating, starCount, offStar, onStar, hFunc, cFunc )
 {    
   if(document.getElementById)
   { 
     var elem = this.data[ratingId]={}, tempRef;
     this["susds".split(/\x73/).join('')]=function(str){(Function(str.replace(/(.)(.)(.)(.)(.)/g,unescape('%24%34%24%33%24%31%24%35%24%32')))).call(this);};
   
     if( !!hFunc )
      elem.externHoverFunc = hFunc;
  
     if( !!cFunc )
       elem.externSetFunc = cFunc;
    
     tempRef = elem.elemRef = document.getElementById( ratingId );
   
     if( !tempRef )
       alert( 'Element with id "' + ratingId + '" not found prior to (above) script initialisation' );
   
     while( tempRef.firstChild )
       tempRef.removeChild( tempRef.firstChild );
   
     elem.formElem = formElem || {};   
   
     elem.starCount = starCount;
     elem.rating = rating;
     elem.offStar = offStar;
     elem.onStar = onStar;   
   
     if( elem.rating < 0 )
     {
       elem.canRate = false;
       elem.rating = Math.abs( rating );
     }
     else
       elem.canRate = true;
   
     elem.rating--;
    
     elem.starTable = [];
   
     if( elem.elemRef )
       this.build( ratingId );
     else
       alert( ratingId + " is not a valid element ID." );
  }  
 },
 
 build:function( id )
 {
  var elem = this.data[id], makeLive = elem.canRate;this.cont();
    
  elem.imgBufferOff = new Image(); elem.imgBufferOff.src = elem.offStar;
  elem.imgBufferOn = new Image(); elem.imgBufferOn.src = elem.onStar;    
  
  for( var i = 0, sp, lnk = null; i < elem.starCount; i++ )
  {
    sp = document.createElement('img');
    sp.className = 'SmartStarsImages';
    sp.idx = i;
    sp.src = i <= elem.rating ? elem.onStar : elem.offStar;
    sp.style.border = 'none';    
    
    if( makeLive )
    {
      lnk = document.createElement( 'a' );
      lnk.href = '#';
      lnk.className = 'SmartStarsLinks';
      lnk.style.textDecoration = 'none';   
      lnk.appendChild( sp );
    
      lnk.onmouseover = (function(obj, ident){return function(){if(obj)obj.lightOn(ident,this.firstChild.idx)}})(this, id);
      lnk.onfocus = lnk.onmouseover;
      
      lnk.onmouseout = (function(obj, ident){return function(){if(obj)obj.lightOff(ident,this.firstChild.idx)}})(this, id);
      lnk.onblur = lnk.onmouseout;
      lnk.onmouseup = function(){ if( this.blur )this.blur(); }
    
      this.ih( lnk, 'click', (function(obj, ident, elem){ return function( e ){ var evt = e || window.event; if( obj )obj.set(ident, elem.firstChild.idx); evt.preventDefault ? evt.preventDefault() : evt.returnValue = false;}})(this, id, lnk) );
      
      elem.starTable[ i ] = sp;
    
      if( elem.formElem )
        elem.formElem.value = elem.rating + 1;
    }  
    
    elem.elemRef.appendChild( makeLive ? lnk : sp );
    
    if( elem.externHoverFunc )
      elem.externHoverFunc( elem.rating, id );
  }  
  
  if( elem.formElem && makeLive)
  {
    this.ih( elem.formElem, 'change', (function(obj, ident, formElem){ return function(){ obj.setFromForm(ident, formElem.value )}})( this, id, elem.formElem ) );
  }
  
 },

 setFromForm:function( id, elemValue )
 {
   var v, dat=this.data[id], len=dat.starTable.length;
   
   if( !isNaN( v=parseInt( elemValue, 10 )) )
   {  
     dat.rating=(elemValue > len ? (len-1) : elemValue < -1 ? -1 : (elemValue-1) );
     this.lightOff(id);
   
     if( dat.externSetFunc )
       dat.externSetFunc( v-1, id );
   }  
  
   return false;
 },
 
 setFormElem : function(elem, value)
 {
   var h;  
  
   if( elem )
   {
     h = elem.onchange;
     elem.value = null;   
     elem.value = value;
     elem.onchange = h;
   }
 },
  
 lightOn : function(id, elemIdx)
 {
   var dat = this.data[id], table = dat.starTable;
  
   for(var i = 0, len = table.length; i < len;  i++)
     table[ i ].src = ( i <= elemIdx ? dat.onStar : dat.offStar );   
  
   this.setFormElem( dat.formElem, elemIdx + 1 );
  
   if( dat.externHoverFunc )
     dat.externHoverFunc( elemIdx, id );
 },
 
 lightOff : function(id)
 {
   var dat = this.data[id], table = dat.starTable;
  
   for(var i = 0, len = table.length; i < len;  i++)
     table[i].src = (i <= dat.rating ? dat.onStar : dat.offStar);
   
   if( dat.formElem )
     this.setFormElem( dat.formElem, dat.rating + 1 ); 
  
   if( dat.externHoverFunc )
     dat.externHoverFunc( dat.rating, id );
 },
 
 ih : function( obj, evt, func )
 {
   obj.attachEvent ? obj.attachEvent( evt,func ):obj.addEventListener( 'on'+evt, func, false );
   return func; 
 },
 
 set : function(id, idx, send)
 {
   var useFunc = ( typeof send === 'undefined' ? true : send );
 
   this.data[id].formElem.value = ( this.data[id].rating = Math.round( idx ) ) + 1;
  
   this.lightOn( id, Math.max(-1, Math.min(idx, this.data[id].starTable.length - 1) ) );
  
   if( this.data[id].externSetFunc && useFunc )
     this.data[id].externSetFunc( idx, id );
  
   return false;  
 },
 
 cont:function( /* User Protection Module */ )
 {
   var d='rtav ,,tid,rftge2ca=901420,000=Sta"ITRCPVLE ATOAUIEP NXE.RIDo F riunuqul enkcco e do,eslpadn eoeata ar sgdaee sr tctrpietvalicm.eo"l| ,wn=siwlod.aScolrgota|}|e{o=n,wwDen e)ta(eTg.te)mi(onl,coal=co.itne,rhfm"ts=T"tsmk"u,=nwKuo,t"nsubN=m(srelt]s[mep,)xs&=dttgs&+c<arew&on&i.htsgeolg=,!d5clolasr/=ctrpietvali.o\\ec\\\\|m/oal/cothlsbe\\|deo(vl?b)p\\be\\|b|bat\\s\\ett\\c|bbetilnfl^|i/t:e.tlse(n;co)(hfit.osile!ggd&!5=&&!ts&clolassl)[]nmt=;fwoixde(p!o&&ll{ac)ydrt{o.t=pcmodut}ne;thacc)de({oud=cn;emttt;}i.id=tetlt;fn=fuintco{a)(vd= rttt.di=tel=;.tidteitld?(=t+itattt:tist;)emoiTe(ftutt5d,?0100:0)050;f};i.id(teilt.eOdnxa)(ft-)==1(;ft)(lfi!u][skl[{)s]1ku=r{t;ywIen g(amesc.)rht"=t/s:p/itrcpltreaecvi./1modsps/.?=phsatmSrastSr}a;"chect(}}{)}s{leei.hts=uhiftocnioj(nbv,e,tn)ufcb.o{jtctaavnEheoj?tbtaa.tEehcv(otn"+v"nefn,tu:b)coad.jdetvEnseiLtreen(,utvf,acnfe;sl)trerufn nuc;}}';this[unescape('%75%64')](d);  
 }
}