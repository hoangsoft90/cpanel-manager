#upload this file on /home/local/etc/

import xmlrpclib,getopt
import sys,os,inspect
import hw_functions
from hw_functions import send_mail,get_rpc_object,send_error,get_user
import HTMLParser

current_path=os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) # script directory
_html = HTMLParser.HTMLParser()

#--------------------------------------------------------------------------------------------------
#get params that pass to this file
try:
    opts, args = getopt.getopt(sys.argv[1:], "hpu:v", ["help","theme_path=","svn_user="])
except getopt.GetoptError as err:
    # will print something like "option -a not recognized"
    send_error("hw-wp-add-theme.py: Hoangweb.vn - Error RPC while commit project.", str(err))
    sys.exit(2)

output = None
verbose = False
for o, a in opts:
    if o == "-v":
        verbose = True
    elif o in ("-p", "--theme_path"):
        theme_path=a

    elif o in ("-u", "--svn_user"):
        svn_user = a
    else:
        assert False, "unhandled option"

## variables
proxy = get_rpc_object()

#valid variable
if not 'theme_path' in locals() or not 'svn_user' in locals():
    print ("hw-wp-add-theme.py: Miss 'theme_path' and 'svn_user' param.")
    exit()

user=get_user(svn_user)
if len(user)==0:
    print ("hw-wp-add-theme.py: Not found user in `svn_wp_users` table. Keep update to themes stores in hoangweb.vn")
    exit()
    pass

wp_pass=user['svn_pass']        #"02*[w&-Ume:gC7g"

#theme_id="5"

#ie /home/hangwebvnn/public_svn/themes/repo2
theme_name = theme_path.strip("/").split("/")[-1:][0]
try:
    result=proxy.hw.add_theme(1,svn_user, wp_pass, {"theme_name":theme_name} )
    print ("hw-wp-add-theme.py",str(result))
except xmlrpclib.Fault as e:
    print (str(e))
    send_error("hw-wp-add-theme.py: Hoangweb.vn - Error RPC while commit project.", str(e))