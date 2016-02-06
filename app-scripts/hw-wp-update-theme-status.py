#upload this file on /home/local/etc/

import xmlrpclib,getopt
import sys,inspect,os

import hw_functions
from hw_functions import send_mail,get_rpc_object,send_error,get_user
import HTMLParser

current_path=os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) # script directory
_html = HTMLParser.HTMLParser()
#--------------------------------------------------------------------------------------------------
#get params that pass to this file
try:
    opts, args = getopt.getopt(sys.argv[1:], "hpsu:v", ["help","theme_path=", "theme_status=","svn_user="])
except getopt.GetoptError as err:
    # print help information and exit:
    send_error("hw-wp-update-theme-status.py: Hoangweb.vn - Error RPC while commit project.", str(err)) # will print something like "option -a not recognized"
    sys.exit(2)

output = None
verbose = False
for o, a in opts:
    if o == "-v":
        verbose = True
    elif o in ("-p", "--theme_path"):
        theme_path=a

    elif o in ("-s", "--theme_status"):
        theme_status = a

    elif o in ("-u", "--svn_user"):
        svn_user = a
    else:
        assert False, "unhandled option"

#get theme name
if not 'theme_path' in locals() or not 'theme_status' in locals() or not 'svn_user' in locals():
    print "hw-wp-update-theme-status.py: Miss 'theme_path' and 'theme_status' and 'svn_user' params."
    exit()

## variables
proxy = get_rpc_object()

#wp_user="my123admin"  #"usercode"
user=get_user(svn_user)
if len(user)==0:
    print "hw-wp-update-theme-status.py: Not found user in db. Keep update to themes stores in hoangweb.vn"
    exit()
    pass

wp_pass=user['svn_pass']        #"02*[w&-Ume:gC7g"
#theme_id="5"


#/home/hangwebvnn/public_svn/themes/repo2
theme_name = theme_path.strip("/").split("/")[-1:][0]

try:
    result=proxy.hw.update_theme_status(1,svn_user, wp_pass,{"id": theme_name, "status": str(theme_status)})
    print result
except xmlrpclib.Fault as e:
    print str(e)
    send_error("hw-wp-update-theme-status.py: Hoangweb.vn - Error RPC while commit project.", str(e))