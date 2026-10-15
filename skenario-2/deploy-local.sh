#!/bin/bash

# K3s Deployment Script for AppToko Microservices
# Lab 2 JP (80 Menit) - XII TJKT

set -e

echo "=========================================="
echo "K3s Deployment - AppToko Microservices"
echo "=========================================="

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Build Docker images
echo -e "${YELLOW}[1/4] Building Docker images...${NC}"
docker build -t app-toko:latest -f Dockerfile .
docker build -t nginx-toko:latest -f nginx.Dockerfile .
echo -e "${GREEN}✓ Docker images built successfully${NC}"

# Step 2: Save images for K3s import
echo -e "${YELLOW}[2/4] Saving Docker images for K3s...${NC}"
docker save app-toko:latest -o app-toko.tar
docker save nginx-toko:latest -o nginx-toko.tar
echo -e "${GREEN}✓ Images saved as tar files${NC}"

# Step 3: Import images to K3s
echo -e "${YELLOW}[3/4] Importing images to K3s containerd...${NC}"
sudo k3s ctr images import app-toko.tar
sudo k3s ctr images import nginx-toko.tar
echo -e "${GREEN}✓ Images imported to K3s${NC}"

# Step 4: Apply Kubernetes manifests
echo -e "${YELLOW}[4/4] Applying Kubernetes manifests...${NC}"
kubectl apply -f k8s/00-namespace-secret.yaml
kubectl apply -f k8s/01-db-deployment.yaml
kubectl apply -f k8s/02-app-deployment.yaml
kubectl apply -f k8s/03-nginx-proxy.yaml
echo -e "${GREEN}✓ Kubernetes manifests applied${NC}"

# Wait for pods to be ready
echo -e "${YELLOW}Waiting for pods to be ready...${NC}"
kubectl wait --for=condition=ready pod -l app=mysql -n toko-lab --timeout=120s
kubectl wait --for=condition=ready pod -l app=app-toko -n toko-lab --timeout=120s
kubectl wait --for=condition=ready pod -l app=nginx-proxy -n toko-lab --timeout=120s

# Display deployment status
echo -e "${GREEN}=========================================="
echo "Deployment Complete!"
echo "==========================================${NC}"
echo ""
echo "Pod Status:"
kubectl get pods -n toko-lab
echo ""
echo "Services:"
kubectl get svc -n toko-lab
echo ""
echo -e "${GREEN}Access the application at: http://<VM-IP>:30080${NC}"
echo ""
echo "Default Login Credentials:"
echo "  Username: admin"
echo "  Password: 123"
echo ""
echo -e "${YELLOW}To check logs:${NC}"
echo "  kubectl logs -n toko-lab deployment/app-toko"
echo "  kubectl logs -n toko-lab deployment/mysql"
echo ""
echo -e "${YELLOW}To delete deployment:${NC}"
echo "  kubectl delete namespace toko-lab"
echo ""

# Cleanup tar files
rm -f app-toko.tar nginx-toko.tar
echo -e "${GREEN}✓ Cleanup complete${NC}"
