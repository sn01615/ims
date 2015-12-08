#-*- coding: UTF-8 -*-
'''
@desc IMS 定时任务进程监控脚本
@author YangLong
@date 2015-11-03
'''
import smtplib,os,socket
from email.mime.text import MIMEText

mailto_list=['long.yang@xytinc.com']
mail_host="smtp.exmail.qq.com"
mail_user="ims@xytinc.com"
mail_pass="xyt12345"
mail_postfix="xytinc.com"
def send_mail(to_list,sub,content):
    # me="IMS82"+"<"+mail_user+"@"+mail_postfix+">"
    me="ims ps aux"+"<"+mail_user+">"
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
    fp=os.popen("ps aux | grep -E 'php|CPU' | grep -v grep | grep -E 'ImsJobs|CPU'")
    x=fp.read()
    if not send_mail(mailto_list,"ps aux at "+socket.gethostname(),x):
        print("send fail")
    # else:
    #     print("send fail")
