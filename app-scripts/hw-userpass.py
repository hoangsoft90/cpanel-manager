import json,sys,os
import cPickle,codecs
import base64



def encode(key, clear):
    enc = []
    for i in range(len(clear)):
        key_c = key[i % len(key)]
        enc_c = chr((ord(clear[i]) + ord(key_c)) % 256)
        enc.append(enc_c)
    return base64.urlsafe_b64encode("".join(enc))

def decode(key, enc):
    dec = []
    enc = base64.urlsafe_b64decode(enc)
    for i in range(len(enc)):
        key_c = key[i % len(key)]
        dec_c = chr((256 + ord(enc[i]) - ord(key_c)) % 256)
        dec.append(dec_c)
    return "".join(dec)


args = sys.argv[1:]
_user=args[0]
_pass=args[1]
_db_file="/home/hangwebvnn/svn_users/data-user.db"
_db_file="f:/abc.txt"
key="ksdjgbg!#*@#&*#(~lsdg546445"

if len(args)<2:
    exit()
    pass

if not os.path.isfile(_db_file):
    open(_db_file, 'a').close()

#load all users
f=codecs.open(_db_file,mode='r',encoding='Ascii')
data = f.read()
f.close()
"""
txt=os.open(_db_file,os.O_RDONLY)
data=txt.read()     #read all
"""
if data is None:
    data=""

#print "[",data.strip(),"]"

#parse str into json
if data !="" and not data is None :
    json_data=cPickle.loads(decode(key, data))
else:
    json_data={}

#if _user in json_data:
json_data[_user]=_pass
#   pass

# save change file
t= cPickle.dumps(json_data)
file_ = open(_db_file, 'w')
file_.write(encode(key,t))
file_.close()