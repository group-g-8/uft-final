#include <stdlib.h>
#include <stdio.h>
#include <errno.h>
#include <string.h>
#include <sys/types.h>
#include <netinet/in.h>
#include <sys/wait.h>
#include <sys/socket.h>
#include <signal.h>
#include <ctype.h>          
#include <arpa/inet.h>
#include <netdb.h>

#define PORT 2000
#define LENGTH 1024


void error(const char *msg)
{
	perror(msg);
	exit(1);
}

int main(int argc, char *argv[])
{
	/* Variable Definition */
	int sockfd; 
	int nsockfd;
	char revbuf[LENGTH]; 
	char response[LENGTH]; 
	struct sockaddr_in remote_addr;

	char district[20];
	char username[20];

	printf("Enter Username:	");
	gets(username);
	printf("\n");

	printf("Enter District:	");
	gets(district);
	printf("\n");

	commands();
	while (1)
	{
		/* Get the Socket file descriptor */
		if ((sockfd = socket(AF_INET, SOCK_STREAM, 0)) == -1)
		{
			error("ERROR: Failed to obtain Socket Descriptor!\n");
		}

		/* Fill the socket address struct */
		remote_addr.sin_family = AF_INET; 
		remote_addr.sin_port = htons(PORT); 
		inet_pton(AF_INET, "127.0.0.1", &remote_addr.sin_addr); 
		bzero(&(remote_addr.sin_zero), 8);

		/* Try to connect the remote */
		if (connect(sockfd, (struct sockaddr *)&remote_addr, sizeof(struct sockaddr)) == -1)
		{
			error("ERROR: Failed to connect to the host!\n");
		}
		
		char cmd[LENGTH];

		bzero(cmd, LENGTH);
		printf("client:  ");
		gets(cmd);
		printf("\n");
		if (strcmp(cmd,"1")==0)
        {
			bzero(username, 20);
			bzero(district, 20);
            break;

        }else
        {
			if (strcmp(cmd,"add uft.txt")==0)
			{
				send_file(sockfd,username,district);
			}
			else
			{
				if (strcmp(cmd,"check_status")==0 || strcmp(cmd,"get_statement")==0)
				{
					strncat(cmd," ",2);
					strncat(cmd,username,20);
					send(sockfd, cmd, sizeof(cmd), 0);
					bzero(cmd, LENGTH);

					//recieving from the server
					
					if(recv(sockfd, response, sizeof(response), 0)){
						
						printf("server:  %s\n",response);
						//break;	
					};
					
					bzero(response, LENGTH);
				}
				else{
					//sending data to the server
					strncat(cmd,",",2);
					strncat(cmd,username,20);
					strncat(cmd,",",2);
					strncat(cmd,district,20);	
					send(sockfd, cmd, sizeof(cmd), 0);
					bzero(cmd, LENGTH);

					//recieving from the server
					
					if(recv(sockfd, response, sizeof(response), 0)){
						printf("server:  %s\n",response);	
					};
				}
				
				
				bzero(response, LENGTH);
			}
		}
	}
	
	
	
	close (sockfd);
	printf("[Client] Connection lost.\n");
	return (0);
}


void send_file(int sock, char username[20],char district[20]){
	char line[256];
    char response[256];

	// char command[50];
	// char query[50];

	// sscanf(criteria,"%s %s",command,query);
	FILE *fp;
	fp = fopen("utf.txt","r");
	if (fp==NULL){
		// printf("Error file not found;");
        //break;
	}
	
	while(!feof(fp)){

		fgets(line,256,fp);

        line [ strcspn(line, "\n\r") ] = 0;
        strncat(line,",",2);
        strncat(line,username,20);
        strncat(line,",",2);
        strncat(line,district,20);

		send(sock, line, sizeof(line), 0);
		
        bzero(line, 256);
 	}
	fclose(fp);
	// printf("Ok File %s from Client was Sent!\n", fp);
	recv(sock, response, sizeof(response), 0);
	printf("server:  %s\n",response);
	bzero(response, 256);
	
}

void commands(){
    printf("Please choose from the following commands\n");
    printf("-----------------------------------------------------\n");
    printf("1.  To add member:	Addmember member_name,date,gender,recommender,signature\n");
    printf("2.  To check status:	check_status\n");
    printf("3.  To get a statement:	get_statement\n");
	printf("4.  To add members from file:	add uft.txt\n");
    printf("5.  To search in a file:	search criteria\n");
	printf("6.  To add correct a signature:	signature <sign>\n");
	printf("7.  To exit, press:	1\n");
    printf("-----------------------------------------------------\n");
    
}