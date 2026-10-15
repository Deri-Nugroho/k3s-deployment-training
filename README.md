# K3s Deployment Training

Materi Microservice K3s di Ubuntu 24.04 untuk XII TJKT 1

## Struktur Folder

```
k3s-deployment-training/
├── skenario-1/          # Landing Page Single Container
│   ├── Dockerfile
│   ├── index.html
│   └── k3s-manifest.yaml
├── skenario-2/          # Multi-Container CRUD (4 Container)
│   ├── Dockerfile
│   ├── nginx.Dockerfile
│   ├── nginx.conf
│   ├── config.php
│   ├── deploy-local.sh
│   ├── PANDUAN_LAB_2JP.md
│   ├── k8s/
│   │   ├── 00-namespace-secret.yaml
│   │   ├── 01-db-deployment.yaml
│   │   ├── 02-app-deployment.yaml
│   │   └── 03-nginx-proxy.yaml
│   └── (file aplikasi PHP lainnya)
└── README.md
```

## Skenario 1: Landing Page (Single Container)
- Pengenalan Pod, Deployment, Service NodePort
- Verifikasi K3s cluster
- Durasi: 15 menit

## Skenario 2: Multi-Container CRUD (4 Container)
- Arsitektur: Web App + Nginx Proxy + MySQL + Utility
- Konsep: Service-to-Service Networking, ConfigMap/Secret, Self-healing
- Durasi: 35 menit

## Persyaratan
- VMware Workstation dengan VM Ubuntu 24.04 LTS
- RAM 2-4 GB
- K3s Single-Node Cluster
- Docker terinstall

## Panduan Lengkap
Lihat file `skenario-2/PANDUAN_LAB_2JP.md` untuk panduan praktikum lengkap dengan pembagian waktu 80 menit.

## Akses Aplikasi
- Skenario 1: `http://<IP-VM>:30080`
- Skenario 2: `http://<IP-VM>:30080`
  - Login: admin / 123

## Author
Deri Nugroho | XII TJKT 1
