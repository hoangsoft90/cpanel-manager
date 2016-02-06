"""
this script used to add/update svn user
"""
import sqlite3 as lite
import sys,getopt
import inspect, os

"""
add svn user
"""
def add_user(svn_user,svn_pass):
    try:
        con = lite.connect(current_path+'/user-data.db')

        cur = con.cursor()
        sql="INSERT INTO `users` (svn_user,svn_pass) SELECT * FROM (SELECT '"+str(_user)+"', '"+str(_pass)+"') AS tmp WHERE NOT EXISTS ( SELECT * FROM `users` WHERE svn_user = '"+str(_user)+"' and svn_pass= '"+str(_pass)+"' ) LIMIT 1;"

        print sql
        #cur.executemany(sql, (_user,_pass))
        cur.execute(sql)

    except lite.Error, e:

        print "Error %s:" % e.args[0]
        sys.exit(1)

    finally:

        if con:
            con.close()
    pass

"""
return svn user credential
"""
def get_user(svn_user):
    try:
        con = lite.connect(current_path+'/user-data.db')
        cur = con.cursor()
        sql="select * from users where svn_user='"+svn_user+"' limit 1"
        #print sql
        cur.execute(sql)
        user = cur.fetchone()
        return user

    except lite.Error, e:

        print "Error %s:" % e.args[0]
        sys.exit(1)

    finally:

        if con:
            con.close()

    pass

con = None
args = sys.argv[1:]
_user=args[0]
_pass=args[1]
current_path=os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) # script directory

#get params that pass to this file
try:
    opts, args = getopt.getopt(sys.argv[1:], "hjup:v", ["help","job=", "svn_user=","svn_pass="])
except getopt.GetoptError as err:
    # print help information and exit:
    print(err) # will print something like "option -a not recognized"
    sys.exit(2)

output = None
verbose = False
for o, a in opts:
    if o == "-v":
        verbose = True
    elif o in ("-u", "--svn_user"):
        svn_user=a

    elif o in ("-p", "--svn_pass"):
        svn_pass = a

    elif o in ("-j", "--job"):
        job = a

    else:
        assert False, "unhandled option"


#validation
if not 'svn_user' in locals() or not 'job' in locals():
    print "Miss 'svn_user' and 'job' params."
    exit()

#print job

if job=="add_user" and 'svn_user' in locals() :
    add_user(svn_user, svn_pass)

elif job == "get_user":
    print get_user(svn_user)
    pass