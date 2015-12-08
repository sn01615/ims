#-*- coding: UTF-8 -*-
'''
@desc IMS 定时任务超时进程自动杀脚本
@author YangLong
@date 2015-11-03
'''
import os,re,time,smtplib,socket
from email.mime.text import MIMEText

mailto_list=['long.yang@xytinc.com']
mail_host="smtp.exmail.qq.com"
mail_user="ims@xytinc.com"
mail_pass="xyt12345"
mail_postfix="xytinc.com"
def send_mail(to_list,sub,content):
    # me="IMS82"+"<"+mail_user+"@"+mail_postfix+">"
    me="ims ps kill"+"<"+mail_user+">"
    msg = MIMEText(content,_subtype='plain',_charset='gb2312')
    msg['Subject'] = sub
    msg['From'] = me
    msg['To'] = ";".join(to_list)
    try:
        server = smtplib.SMTP()
        server.connect(mail_host)
        server.login(mail_user,mail_pass)
        server.sendmail(me, to_list, msg.as_string())
        server.close()
        return True
    except Exception as e:
        print(str(e))
        return False

if __name__ == '__main__':
    time.sleep(15)
    fp=os.popen("ps aux | grep php | grep -v grep | grep ImsJobs")
    #fp=os.popen("ifconfig")
    x=fp.readlines()
    mailtext='ims kill list:\n'
    for l in x:
        lre=re.split(r'\s+',l)
        # print(lre[1]+' '+lre[8])
        sch=re.search(r'\d+:\d+',lre[8])
        if not sch is None:
            pt=time.strptime(time.strftime("%Y-%m-%d ")+lre[8],'%Y-%m-%d %H:%M')
            if time.time()-time.mktime(pt)>3600:
                # print(time.time()-time.mktime(pt))
                fp=os.popen("kill "+lre[1])
                mailtext+=l
        else:
            fp=os.popen("kill "+lre[1])
            mailtext+=l
    if len(mailtext)>15:
        send_mail(mailto_list,"ims kill at "+socket.gethostname(),mailtext)
