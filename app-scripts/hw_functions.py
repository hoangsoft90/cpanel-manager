import xmlrpclib,smtplib
import sys
import requests,json,cgi
from urllib import quote
import hw_config

"""
send mail using SMTP
"""
def send_mail(sender,recipient,subject, body,html=True):
    SMTP_SERVER = 'smtp.mail.yahoo.com'
    SMTP_PORT = 587

    session = smtplib.SMTP(SMTP_SERVER, SMTP_PORT)
    #server response
    session.ehlo()
    #put the SMTP connection in TLS
    session.starttls()
    #run ehlo again to see server response
    session.ehlo()
    #login to gmail
    session.login('quachhoang_2005@yahoo.com', 'Hoangcode837939')
    #prep mail header
    headers = ["from: " + str(sender),
               "subject: " + str(subject),
               "to: " + str(recipient),
               "mime-version: 1.0",
               "content-type: text/html"]
    headers = "\r\n".join(headers)
    #finally to send

    if html==False:
        body= quote(body)

    session.sendmail(sender, recipient, headers + "\r\n\r\n" + str(body))

    print "sended email to you!"
    #close SMTP
    session.quit()
    pass

"""
send mail using google script
"""
def send_mail1(to,subject, body,html=True):
    url = 'https://docs.google.com/forms/d/'+hw_config.GOOGLE_FORM_ID+'/formResponse'
    if html ==False:
        body=quote(body)

    #payload = json.load('{"a":"AC"}')
    form_data={
        "entry.1863669676": subject,    #mail subject
        "entry.406014489" : body,  #Tin nhan
        "entry.1247917524" : to         #send mail to
    }
    user_agent= {
        "Referer":"https://docs.google.com/forms/d/"+hw_config.GOOGLE_FORM_ID+"/viewform",
        "Accept-Charset": "UTF-8",
        #"Content-type":"text"
    }

    r = requests.post(url, data=form_data, headers=user_agent
                      #,verify=False
                      )
    return r.text
    pass

"""
return RPC instance
"""
def get_rpc_object(rpc_url=""):
    if rpc_url=="":
        rpc_url=hw_config.HOANGWEB_VN_RPC_URL

    proxy = xmlrpclib.ServerProxy(rpc_url)
    return proxy
    pass

"""
copy: hw-userpass1.py
return svn user credential
"""
def get_user(svn_user):
    # wp admin credential
    admin_wpuser=hw_config.WP_USER
    admin_wppass=hw_config.WP_PASS
    try:
        result = get_rpc_object().hw.get_user(1, admin_wpuser, admin_wppass, {"userlogin":svn_user } )

        if len(result) ==0:
            send_error("hw_functions.py/get_user: Not found svn user", "Not found svn user svn user known as wp user ["+svn_user+"] in `svn_wp_users` table.")
            exit()

        return result
    except xmlrpclib.Fault as e:
        print str(e)
        send_error("hw_functions.py/get_user: Hoangweb.vn - Error RPC while commit project.", str(e))
        sys.exit(2)
    pass

"""
send error to admin
"""
def send_error(subject,msg):
    #send_mail("quachhoang_2005@yahoo.com", ADMIN_EMAIL, subject, msg)
    msg = quote(msg)
    send_mail1(hw_config.ADMIN_EMAIL, subject, msg)
    exit()      #exit when meet error
    pass
