# K3s Deployment Training

Materi Microservice K3s di Ubuntu 24.04 untuk XII TJKT 1

## Struktur Folder

```
k3s-deployment-training/
├── skenario-1/          # Landing Page Single Container
│   ├── Dockerfile
│   ├── index.html
│   ├── k3s-manifest.yaml
│   └── PANDUAN_SKENARIO_1.md
├── skenario-2/          # Multi-Container CRUD (4 Container)
│   ├── Dockerfile
│   ├── nginx.Dockerfile
│   ├── nginx.conf
│   ├── config.php
│   ├── deploy-local.sh
│   ├── PANDUAN_SKENARIO_2.md
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
- VMware Workstation / AWS EC2 dengan VM Ubuntu 24.04 LTS
- RAM 2-4 GB
- K3s Single-Node Cluster
- Docker terinstall (hanya untuk OPSI 1 Skenario 1 dan Skenario 2)

## Setup Awal K3s

### Install K3s
```bash
curl -sfL https://get.k3s.io | sh -
```

### Fix kubectl Permissions (PENTING)
Setelah install K3s, kubectl biasanya tidak bisa diakses oleh user biasa. Jalankan perintah berikut:

```bash
mkdir -p ~/.kube
sudo cp /etc/rancher/k3s/k3s.yaml ~/.kube/config
sudo chown $USER:$USER ~/.kube/config
export KUBECONFIG=~/.kube/config
echo 'export KUBECONFIG=~/.kube/config' >> ~/.bashrc
source ~/.bashrc
```

### Verifikasi K3s
```bash
kubectl get nodes
```
Expected output: Node status `Ready`

### Cek K3s Version
```bash
kubectl version --client
```

## Panduan Praktikum

### Skenario 1: Landing Page

Skenario 1 memiliki **3 opsi deployment** yang bisa dipilih (lihat panduan lengkap di `skenario-1/PANDUAN_SKENARIO_1.md`):

1. **OPSI 1: Manual Build dari Dockerfile** - Build image lokal, import ke K3s
2. **OPSI 2: Deploy dari k3s-manifest.yaml** - Deploy cepat dengan image pre-built
3. **OPSI 3: Deploy via CI/CD GitHub Actions** - Deploy otomatis

**Contoh Deploy Cepat (OPSI 2):**

1. **Clone Repository**
   ```bash
   git clone https://github.com/Deri-Nugroho/k3s-deployment-training.git
   cd k3s-deployment-training/skenario-1
   ```

2. **Deploy Aplikasi Nginx**
   ```bash
   kubectl apply -f k3s-manifest.yaml
   ```
   Expected output:
   ```
   deployment.apps/nginx created
   service/nginx created
   ```

3. **Verifikasi Pod Nginx**
   ```bash
   kubectl get pods
   ```
   Expected output (STATUS `Running`):
   ```
   NAME                     READY   STATUS    RESTARTS   AGE
   nginx-6dfd5df897-qz9t2   1/1     Running   0          18s
   ```

4. **Verifikasi Service Nginx**
   ```bash
   kubectl get svc
   ```
   Expected output (PORT(S) `80:30080/TCP`):
   ```
   NAME         TYPE        CLUSTER-IP      EXTERNAL-IP   PORT(S)        AGE
   kubernetes   ClusterIP   10.43.0.1       <none>        443/TCP        8m26s
   nginx        NodePort    10.43.225.208   <none>        80:30080/TCP   25s
   ```

5. **Akses Aplikasi**
   Buka browser dan akses `http://<IP-VM>:30080`.
   Contoh: `http://54.144.64.253:30080`

6. **Lihat Log Aplikasi (Opsional)**
   ```bash
   kubectl logs deployment/nginx
   ```

7. **Hapus Aplikasi (Opsional)**
   ```bash
   kubectl delete -f k3s-manifest.yaml
   ```
   Expected output:
   ```
   deployment.apps "nginx" deleted from default namespace
   service "nginx" deleted from default namespace
   ```

**Cleanup antar opsi deployment:**
- Jika ingin mencoba opsi lain, lakukan cleanup terlebih dahulu sesuai panduan di `skenario-1/PANDUAN_SKENARIO_1.md`

**Total Cleanup sebelum Skenario 2:**
```bash
# Hapus semua deployment
kubectl delete deployment --all -n default
kubectl delete service --all -n default

# Hapus repository lokal
cd ~
rm -rf k3s-deployment-training

# Hapus kubeconfig
rm -rf ~/.kube

# Uninstall K3s (opsional)
sudo /usr/local/bin/k3s-uninstall.sh
```

### Skenario 2: Multi-Container CRUD
Lihat file [`skenario-2/PANDUAN_SKENARIO_2.md`](skenario-2/PANDUAN_SKENARIO_2.md) untuk panduan lengkap deployment microservices CRUD dengan 4 container.

## Akses Aplikasi
- Skenario 1: `http://<IP-VM>:30080`
- Skenario 2: `http://<IP-VM>:30080`
  - Login: admin / 123

## Author
Deri Nugroho | XII TJKT 1
