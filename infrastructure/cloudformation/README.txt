
cf_helper_initial.yaml - this template should be run first and only once. It creates resources needed to workaround an issue in a backend template. A stack it creates can be safely deleted after the second template is finished. All created by this template resources will be removed except ECR repository which is needed for backend template. It expects only one parameter APPNAME and it should be the same as you specify while deploying backend and frontend.

cf_backend.yaml - this is the template that creates all needed resources and services for backend. It expect such parameters:
    APPNAME - Name of the app, it is used to construct resources names
    GitHubBackendRepo - Backend GitHub repository in format user/repo-name or organization-name/repo-name
    GitHubBackendBranch - Backend (api) branch name that will trigger the pipeline (e.g. main, dev)
    DockerHubUsername - DockerHub username for pulling base images
    DockerHubPassword - DockerHub password for pulling base images
    GhConnectionArn - ARN of the connection to GitHub (https://docs.aws.amazon.com/dtconsole/latest/userguide/connections-create-github.html), it should be created manually before deploying the template.

 cf_frontend.yaml - this is a template that creates frontend resources. It can be run in two ways ("modes"):
     1. A first deployment of the frontend in the current namespace (APPNAME). Along with Cloudfront other required resources are also created, these resources are used by all subsequent cloudfront distributions. In this "mode" template you should provide such parameters:
         APPNAME - the same value for all 3 templates in an application or application environment
         GitHubFrontendRepo  - Frontend GitHub repository in format user/repo-name or organization-name/repo-name
         GitHubFrontendBranch - Frontend branch name that will trigger the pipeline (e.g. main, dev)
         GhConnectionArn - ARN of the connection to GitHub (https://docs.aws.amazon.com/dtconsole/latest/userguide/connections-create-github.html), it should be created manually before deploying the template. You can use the same connection as for the backend if it allows access to both repositories.
         BackendStackName - Name of the backend stack. It uses to find exported values of backend endpoints
         FirstFrontendDeploy=true - A boolean flag to enable this "mode"
      2. All subsequent deployments. In this mode only Cloudfront distribution is deployed. It is configured using shared resources (like roles and identities) created in the first deployment of frontend. Parameters required:
          APPNAME - the same value for all 3 templates in an application or application environment
          BackendStackName - Name of the backend stack. It uses to find exported values of backend endpoints
Please notice, by default the only cloudfront distribution that is being invalidated automatically (on code change in repository) is the one created in the first frontend deployment. To add others just go to CodeBuild and add a command(s) in the end of Buildspec:
    aws cloudfront create-invalidation --distribution-id <Cloudfront Distribution ID> --paths "/*"


All templates should be deployed with exact the same parameter APPNAME. Please wait around 3-5 minutes before running the main templates after the deployment of the template cf_helper_initial.yaml is finished.


Example of usage:

# Deploy only once before running other templates.
aws --region us-east-1 cloudformation deploy --template-file cf_helper_initial.yaml --stack-name fora-stage-initial-helper --parameter-overrides APPNAME=fora-stage --capabilities CAPABILITY_NAMED_IAM

# Deploy a backend
aws --region us-east-1 cloudformation deploy --template-file cf_backend.yaml --s3-bucket fora-stage-cf-template --stack-name fora-backend-stage --parameter-overrides APPNAME=fora-stage GitHubBackendRepo=example-gh-user/fora-api GitHubBackendBranch=dev DockerHubUsername=dh-username DockerHubPassword=dh-password GhConnectionArn=arn:aws:codestar-connections:us-east-1:925811392742:connection/273e2f2e-13a7-4482-a3c7-bd94241bc65a --capabilities CAPABILITY_NAMED_IAM

# First deployment of a frontend
aws --region us-east-1 cloudformation deploy --template-file cf_frontend.yaml --stack-name fora-frontend-stage --parameter-overrides APPNAME=fora-stage GitHubFrontendRepo=example-gh-user/fora-frontend GitHubFrontendBranch=main GhConnectionArn=arn:aws:codestar-connections:us-east-1:925811392742:connection/273e2f2e-13a7-4482-a3c7-bd94241bc65a BackendStackName=fora-backend-stage FirstFrontendDeploy=true --capabilities CAPABILITY_NAMED_IAM

# The second frontend deployment. It will deploy only Cloudfront distribution, all other needed resources were deployed in a previous (first) deployment of the frontend.
aws --region us-east-1 cloudformation deploy --template-file cf_frontend.yaml --stack-name fora-frontend2-stage --parameter-overrides APPNAME=fora-stage BackendStackName=fora-backend-stage
