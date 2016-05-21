/**
 * gcc imsjobs.c -o imsJobs -std=c99 -lpthread
 */
#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <string.h>
#include <pthread.h>
#include <time.h>
#include <sys/file.h>
// #include <sys/types.h>
// #include <sys/stat.h>
// #include <fcntl.h>

#define THREAD_NUM 100

void *thread_function(void *arg);
int i_diff(int arg);

int main()
{
    char message[THREAD_NUM][100];
    int result;
    pthread_t threads[THREAD_NUM];
    void *thread_result[THREAD_NUM];
    FILE *fp;
    time_t t;
    char filename[30];
    char str[15];
    struct tm *timeinfo;

    // 文件名
    sprintf(filename, "/tmp/ImsJobsMaster.lock");
    // 打开文件
    fp = fopen(filename, "w+");
    fclose(fp);
    fp = fopen(filename, "r+");
    if(flock(fp->_fileno, LOCK_EX|LOCK_NB)!=0){
        // printf("running...\n");
        exit(EXIT_SUCCESS);
    }

    for (int i = 0; i < THREAD_NUM; ++i)
    {
        sprintf(message[i], "/usr/bin/php /var/wwwroot/ims/yiicmd.php crontab/Runing/ImsJobs%03d > /tmp/ImsJobs%03d.log 2>&1", i + 1, i + 1);
    }
    for ( ; ; )
    {
        for (int i = 0; i < THREAD_NUM; ++i)
        {
            // 文件名
            sprintf(filename, "/tmp/ImsJobs%03d.lock", i + 1);
            // 打开文件
            fp = fopen(filename, "rt");
            if (!fp)
            {
                fp = fopen(filename, "wt+");
                fprintf(fp, "%d", 0);
                fclose(fp);
                fp = fopen(filename, "rt");
            }
            fgets(str, 15, fp);
            // printf(">>>>>>>>%s<<<<<<<<<<\n", str);
            fclose(fp);
            t = time(0);
            if (t - atoi(str) > i_diff(i))
            {
                // 写文件
                fp = fopen(filename, "wt+");
                fprintf(fp, "%d", t);
                fclose(fp);
                // 创建线程
                result = pthread_create(&threads[i], NULL, thread_function, message[i]);
                if (result != 0)
                {
                    perror("Thread creation failed");
                    exit(EXIT_FAILURE);
                }
            }
            else
            {
                // printf("x: %i %i %i \n", atoi(str), t, t - atoi(str));
            }
        }
        t = time(NULL);
        timeinfo = localtime(&t);
        if (timeinfo->tm_hour == 3 && timeinfo->tm_min == 0)
        {
            break;
        }
        sleep(5);
    }
    for (int i = 0; i < THREAD_NUM; ++i)
    {
        // pthread_detach(threads[i]);
        result = pthread_join(threads[i], &thread_result[i]);
        if (result != 0)
        {
            perror("THread join failed");
            exit(EXIT_FAILURE);
        }
        // printf("Thread joined, it returned %s\n", thread_result[i]);
        // printf("Message is now %s\n", message[i]);
    }
    exit(EXIT_SUCCESS);
}

void *thread_function(void *arg)
{
    // printf("thread_function is running. Argument was %s\n", arg);
    // strcpy(message, "Bye!");
    system(arg);
    // system("php -r \"sleep(rand(0,10));\" ");
    pthread_detach(pthread_self());
    pthread_exit(EXIT_SUCCESS);
}

int i_diff(int arg)
{
    int x;
    if( arg <= 40 )
    {
        x = 60;
    }
    else if(arg<=50)
    {
        x = 5*60;
    }
    else if(arg<=60)
    {
        x = 10*60;
    }
    else if(arg<=70)
    {
        x = 60*60;
    }
    else if(arg<=80)
    {
        x = 24*60*60;
    }
    else if(arg<=90)
    {
        x = 7*24*60*60;
    }
    else
    {
        x = 30*24*60*60;
    }
    return x;
}
