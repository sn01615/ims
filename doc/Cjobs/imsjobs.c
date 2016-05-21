/**
 * gcc imsjobs.c -o imsJobs -std=c99 -lpthread
 */
#include <pthread.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/file.h>
#include <time.h>
#include <unistd.h>

#define THREAD_NUM 100

void *thread_function(void *arg);
int i_diff(int arg);

int main() {
  char message[THREAD_NUM][100];
  int result;
  pthread_t threads[THREAD_NUM];
  void *thread_result[THREAD_NUM];
  FILE *fp;
  time_t t;
  char filename[30];
  char str[15];
  struct tm *timeinfo;

  sprintf(filename, "/tmp/ImsJobsMaster.lock");
  fp = fopen(filename, "w+");
  fclose(fp);
  fp = fopen(filename, "r+");
  if (flock(fp->_fileno, LOCK_EX | LOCK_NB) != 0) {
    exit(EXIT_SUCCESS);
  }

  for (int i = 0; i < THREAD_NUM; ++i) {
    sprintf(
        message[i],
        "/usr/bin/php /var/wwwroot/ims/yiicmd.php crontab/Runing/ImsJobs%03d",
        i + 1);
  }
  for (;;) {
    for (int i = 0; i < THREAD_NUM; ++i) {
      sprintf(filename, "/tmp/ImsJobs%03d.lock", i + 1);
      fp = fopen(filename, "rt");
      if (!fp) {
        fp = fopen(filename, "wt+");
        fprintf(fp, "%d", 0);
        fclose(fp);
        fp = fopen(filename, "rt");
      }
      fgets(str, 15, fp);
      fclose(fp);
      t = time(0);
      if (t - atoi(str) > i_diff(i)) {
        fp = fopen(filename, "wt+");
        fprintf(fp, "%d", t);
        fclose(fp);
        // 创建线程
        result = pthread_create(&threads[i], NULL, thread_function, message[i]);
        if (result != 0) {
          perror("Thread creation failed");
          exit(EXIT_FAILURE);
        }
      }
    }
    t = time(NULL);
    timeinfo = localtime(&t);
    if (timeinfo->tm_hour == 3 && timeinfo->tm_min == 0) {
      break;
    }
    sleep(5);
  }
  for (int i = 0; i < THREAD_NUM; ++i) {
    result = pthread_join(threads[i], &thread_result[i]);
    if (result != 0) {
      // perror("THread join failed");
      // exit(EXIT_FAILURE);
    }
  }
  pthread_exit(NULL);
  // exit(EXIT_SUCCESS);
}

void *thread_function(void *arg) {
  system(arg);
  pthread_detach(pthread_self());
  pthread_exit(EXIT_SUCCESS);
}

int i_diff(int arg) {
  int x;
  if (arg <= 40) {
    x = 60;
  } else if (arg <= 50) {
    x = 5 * 60;
  } else if (arg <= 60) {
    x = 10 * 60;
  } else if (arg <= 70) {
    x = 60 * 60;
  } else if (arg <= 80) {
    x = 24 * 60 * 60;
  } else if (arg <= 90) {
    x = 7 * 24 * 60 * 60;
  } else {
    x = 30 * 24 * 60 * 60;
  }
  return x;
}
