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
    opts, args = getopt.getopt(sys.argv[1:], "hst:v", ["help","body=","type=","svn_user=","only_first_commit="])
except getopt.GetoptError as err:
    # print help information and exit:
    send_error("Hoangweb.vn - Error RPC while commit project.", str(err)) # will print something like "option -a not recognized"
    sys.exit(2)


for o, a in opts:
    if o == "-v":
        verbose = True
    elif o in ("-s", "--body"):
        body=a

    elif o in ("-t", "--type"):
        type=a
    elif o in ("-u", "--svn_user"):
        svn_user=a

    elif o in ("-u", "--only_first_commit"):
        only_first_commit=a

    else:
        assert False, "unhandled option"

## variables
output = None
verbose = False
proxy = get_rpc_object()

#validation
if not 'svn_user' in locals():
    print ("hw-mailer.py: Miss 'svn_user' param.")
    exit()
    pass

_user=get_user(svn_user)

if len(_user)==0:
    print ("hw-mailer.py: Not found svn user known as wp user ["+svn_user+"].")
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
    result=proxy.hw.mail_commit_theme(1,svn_user, wp_pass, {
        "subject":subject, "body": body,
        "user_only_first_commit":only_first_commit,
        "repository":theme_repo
    } )

    print ("hw-mailer.py: "+_html.unescape(result))
except xmlrpclib.Fault as e:
    print (str(e))
    send_error("Hoangweb.vn - Error RPC while commit project.", str(e))
    pass