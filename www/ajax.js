// JavaScript Document
var scrolling=true;
$(document).ready(function()
{

$('form[name=post_mesg]').submit(function()
{
return false;
});

$('textarea[name=new_mesg]').keypress(function(e) 
	{
        if(e.which == 13) 
		{
		$.post('/handlers/msg_handler.php', {'s': $('input[name=s]').val(),'URI':$('#URI').html(), 'new_mesg': encodeURIComponent($('textarea[name=new_mesg]').val())}, function(data)
		{
		$('textarea[name=new_mesg]').val('');
		return true;
		});
		
		
		return true;
            
        }
    });

/*
$('input[name=post_mesg_submit]').click(function()
{
$.post('/handlers/msg_handler.php', {'s': $('input[name=s]').val(),'URI':$('#URI').html(), 'new_mesg': encodeURIComponent($('textarea[name=new_mesg]').val())}, function(data)
{
$('textarea[name=new_mesg]').val('');
return true;
});
*/

$('input[name=new_pwd_val]').change(function()
{
//alert('123');
$.post('/handlers/validator.php',{'s': $('input[name=s]').val(), 'pwd': $('input[name=new_pwd_val]').val()},function(data)
	{
//	alert (data);
	if (data==1) $('input[name=new_pwd_val]').attr("style", 'background-color:#FF0000');
	if (data==2) $('input[name=new_pwd_val]').attr("style", 'background-color:#DD1100');
	if (data==3) $('input[name=new_pwd_val]').attr("style", 'background-color:#CC2200');
	if (data==4) $('input[name=new_pwd_val]').attr("style", 'background-color:#BB3300');
	if (data==5) $('input[name=new_pwd_val]').attr("style", 'background-color:#AA4400');
	if (data==6) $('input[name=new_pwd_val]').attr("style", 'background-color:#995500');
	if (data==7) $('input[name=new_pwd_val]').attr("style", 'background-color:#886600');
	if (data==8) $('input[name=new_pwd_val]').attr("style", 'background-color:#777700');
	if (data==9) $('input[name=new_pwd_val]').attr("style", 'background-color:#668800');
	if (data==10) $('input[name=new_pwd_val]').attr("style", 'background-color:#559900');
	});
});

$('#channel_mesg_placeholder').hover(
  function () 
  {
	scrolling=false;
  },
  function () 
  {
	scrolling=true;  
  }
);

$('input[name=adduser2channel]').change(function()
{
	$.post('/handlers/validator.php',{'s': $('input[name=s]').val(), 'select_U': $('input[name=adduser2channel]').val()},function(data)
	{
	if (data=='Такой пользователь существует!')	$('input[name=adduser2channel]').attr("style", 'background-color:#00CC00');
	else $('input[name=adduser2channel]').attr("style", 'background-color:#CC0000');
	
	$('input[name=adduser2channel]').attr("title", data);
	});
});

$('input[name=create_new_user]').change(function()
{
	$.post('/handlers/validator.php',{'s': $('input[name=s]').val(), 'new_U': $('input[name=create_new_user]').val()},function(data)
	{
	if (data=='Имя пользователя свободно!')	$('input[name=create_new_user]').attr("style", 'background-color:#00CC00');
	else $('input[name=create_new_user]').attr("style", 'background-color:#CC0000');
	
	$('input[name=create_new_user]').attr("title", data);
	});
});
///
});

function check_new_comments(URI,s)
{
var num_comments;
var c;
$.post('/handlers/msg_handler.php', {'s': s,'URI': URI,'count': 1}, function(data) 
		{
		if(data>$('#num_mesg').html()+1)
			{
			num_comments=data;	
			c=data-$('#num_mesg').html();	
			$('#num_mesg').html(data);
			$.post('/handlers/msg_handler.php', {'s': s,'URI': URI,'list': c}, function(data)
			{
			$('#num_mesg').remove();
			$('#channel_mesg_placeholder').append(data);
			$('#num_mesg').html(num_comments);
			$('#new_comment').fadeOut()  .fadeIn();
			$('#new_comment').attr("id",'');
			scrolling=true;
			});			
			}		
		}); 

if(scrolling) $('#channel_mesg_placeholder').scrollTop(1000*$('#num_mesg').html()+2000);
}

function get_time()
{
//alert('time');
$.post('/handlers/time_handler.php', {'s': 2}, function(data){ $('#server_time').html(data); });	
}

setInterval("get_time()",500);
/////////////////////////////b