pipeline {
    agent any

    environment {
        // Define environment variables
        DOCKER_HUB_CREDENTIALS = credentials('DOCKER_HUB_CREDENTIALS')
    }

    stages {
        stage('Build Docker Image') {
            when {
                anyOf {
                    branch 'develop';
                    branch 'staging';
                }
            }
            steps {
                script {
                    // Build the Docker image
                    dockerImage = docker.build("amalitechservices/resource-manager-backend:latest")
                }
            }
        }

        stage('Push to Docker Hub') {
            when {
                anyOf {
                    branch 'develop';
                    branch 'staging';
                }
            }
            steps {
                script {
                    // Log in to Docker Hub
                    withCredentials([[$class: 'UsernamePasswordMultiBinding', credentialsId: 'DOCKER_HUB_CREDENTIALS', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD']]) {
                        docker.withRegistry('https://registry.hub.docker.com', 'DOCKER_HUB_CREDENTIALS') {
                            // Push the Docker image to Docker Hub
                            dockerImage.push()
                        }
                    }
                }
            }
        }

        stage('Deploy') { 
            when {
                    anyOf {
                        branch 'develop';
                        branch 'staging';
                    }
                }
            steps {
                script{
                        withCredentials([
                            file(credentialsId: 'EC2_SSH_KEY', variable: 'EC2_SSH_KEY'), 
                            usernamePassword(credentialsId: 'EC2_CRED', usernameVariable: 'USERNAME', passwordVariable: 'HOST_IP')]) {
                            sh 'cp $EC2_SSH_KEY ./sshkey'
                            sh 'chmod 600 sshkey'
                            sh """
                                ssh -i sshkey -o StrictHostKeyChecking=no $USERNAME@$HOST_IP '\
                                cd /home/ubuntu/resource-manager && \
                               # sudo docker pull amalitechservices/resource-manager-backend:latest && \
                               # // sudo docker rm -f rm-backend && \
                               # // sudo docker run -d --env-file .env -p 8003:8003/tcp --name rm-backend amalitechservices/resource-manager-backend:latest'
                               cat docker-compose.yaml
                            """

                        }
                    }
            }
        }

        stage("CleanUp"){
            when {
                anyOf {
                    branch 'develop';
                    branch 'staging';
                }
            }
            steps{
                sh 'docker rmi amalitechservices/resource-manager-backend:latest'
                sh "docker logout"
                cleanWs()
                }
        }
    
        
    }
    
}
