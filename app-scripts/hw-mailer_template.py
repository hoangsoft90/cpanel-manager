#upload this file on /home/local/etc/

#end of the header and is required
import smtplib,getopt
import sys,os,inspect
import xmlrpclib
import hw_functions
from hw_functions import send_mail,get_rpc_object,send_error,get_user
import HTMLParser

current_path=os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) # script directory
_html = HTMLParser.HTMLParser()
#--------------------------------------------------------------------------------------------------
## get params that passing to this script
try:
    accept_args=["help","body=","type=","svn_user=","tpl=","repository=","svn_pass="]
    opts, args = getopt.getopt(sys.argv[1:], "hst:v", accept_args)
except getopt.GetoptError as err:
    # print help information and exit:
    send_error("Hoangweb.vn - Error RPC while commit project.", str(err)) # will print something like "option -a not recognized"
    sys.exit(2)

#all arguments data
data={}
for o, a in opts:
    data[o.replace('-','').replace('--','')] = a

    if o == "-v":
        verbose = True
    elif o in ("-s", "--body"):
        body=a

    elif o in ("-t", "--type"):
        type=a
    elif o in ("-u", "--svn_user"):
        svn_user=a

    elif o in ("-u", "--tpl"):
        tpl=a

    elif o in ("-u", "--repository"):
        repository=a
        pass

    elif o in ("-u", "--svn_pass"):
        svn_pass=a
        pass

    else:
        assert False, "unhandled option"

## variables
output = None
verbose = False
proxy = get_rpc_object()

#validation
if not 'svn_user' in locals():  #require params
    print ("hw-mailer_template.py: Miss 'svn_user' param.")
    exit()
    pass

_user=get_user(svn_user)

if len(_user)==0:
    print ("hw-mailer_template.py: Not found svn user known as wp user ["+svn_user+"].")
    exit()
    pass

## mail
subject = 'svn.hoangweb.vn'     #email subject

wp_pass=_user['svn_pass']

# parse mail body
if 'type' in locals() and type=="svn_theme_working_copy":
    theme_repo = body.strip("/").split("/")[-1:][0]
    body="http://svn.hoangweb.vn/themes/%s" % theme_repo
    pass


## start sending mail
try:

    result=proxy.hw.mail_template(1,svn_user, wp_pass, {
        "subject":subject, "body": body,
        "tpl": tpl,
        "type" : type,
        "data": data
    } )
    print ("hw-mailer_template.py: "+_html.unescape(result))
except xmlrpclib.Fault as e:
    print (str(e))
    send_error("Hoangweb.vn - Error RPC while commit project.", str(e))
    pass