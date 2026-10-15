# PANDUAN LAB K3S - MICROSERVICE CRUD
## Materi: K3s di Ubuntu 24.04 | XII TJKT 1 | 2 Jam Pelajaran (80 Menit)

---

## 📋 TUJUAN PRAKTIKUM
- Memahami konsep **Microservices** dengan Kubernetes (K3s)
- Deploy aplikasi **Landing Page** (Single Container)
- Deploy aplikasi **CRUD Multi-Container** (Web App + Nginx Proxy + Database)
- Memahami **Service Networking**, **ConfigMap/Secret**, dan **Self-healing**

---

## 🏗️ ARSITEKTUR INFRASTRUKTUR
- **Hypervisor**: VMware Workstation
- **OS**: Ubuntu 24.04 LTS (VM per siswa)
- **Orchestrator**: Single-Node K3s Cluster
- **Metode Akses**: NodePort via IP VM (`http://<IP-VM>:30080`)
- **Isolasi**: Semua service berjalan lokal di dalam Pod/Cluster K3s

---

## ⏱️ JADWAL PRAKTIKUM

### [00-15 Menit] SETUP K3S & VERIFIKASI NODE

#### 1. Install K3s (jika belum)
```bash
curl -sfL https://get.k3s.io | sh -
```

#### 2. Verifikasi K3s Running
```bash
sudo systemctl status k3s
kubectl get nodes
```
**Expected Output**: Node status `Ready`

#### 3. Cek K3s Version
```bash
kubectl version --short
```

---

### [15-30 Menit] SKENARIO 1: LANDING PAGE (Single Container)

#### 1. Pindah ke folder cohort-01
```bash
cd ~/cohort-01-main
```

#### 2. Deploy Landing Page
```bash
kubectl apply -f k3s-manifest.yaml
```

#### 3. Cek Pod & Service
```bash
kubectl get pods
kubectl get svc
```

#### 4. Akses di Browser
Buka browser dan akses:
```
http://<IP-VM>:30080
```
**Expected**: Tampilan landing page HTML

#### 5. Cleanup Skenario 1
```bash
kubectl delete -f k3s-manifest.yaml
```

---

### [30-65 Menit] SKENARIO 2: MULTI-CONTAINER CRUD (4 Container)

#### Arsitektur:
- **Container 1**: MySQL Database (Deployment + ClusterIP)
- **Container 2**: PHP Web App CRUD (2 replicas + ClusterIP)
- **Container 3**: Nginx Reverse Proxy (Deployment + NodePort)
- **Container 4**: Utility (ConfigMap/Secret untuk env vars)

#### 1. Pindah ke folder appToko
```bash
cd ~/appToko-main
```

#### 2. Build Docker Images
```bash
docker build -t app-toko:latest -f Dockerfile .
docker build -t nginx-toko:latest -f nginx.Dockerfile .
```

#### 3. Import Images ke K3s
```bash
docker save app-toko:latest -o app-toko.tar
docker save nginx-toko:latest -o nginx-toko.tar
sudo k3s ctr images import app-toko.tar
sudo k3s ctr images import nginx-toko.tar
```

#### 4. Apply Kubernetes Manifests
```bash
kubectl apply -f k8s/00-namespace-secret.yaml
kubectl apply -f k8s/01-db-deployment.yaml
kubectl apply -f k8s/02-app-deployment.yaml
kubectl apply -f k8s/03-nginx-proxy.yaml
```

#### 5. Verifikasi Deployment
```bash
# Cek semua pod di namespace toko-lab
kubectl get pods -n toko-lab

# Cek services
kubectl get svc -n toko-lab

# Cek logs jika ada error
kubectl logs -n toko-lab deployment/app-toko
kubectl logs -n toko-lab deployment/mysql
```

#### 6. Akses Aplikasi CRUD
Buka browser dan akses:
```
http://<IP-VM>:30080
```

**Login Default:**
- Username: `admin`
- Password: `123`

#### 7. Uji Fitur CRUD
- **Dashboard**: Lihat statistik
- **Data Barang**: Tambah barang baru (dengan upload foto)
- **Kategori**: Tambah kategori baru
- **POS (Kasir)**: Lakukan transaksi penjualan
- **Laporan**: Lihat laporan penjualan
- **Kelola User**: Tambah user baru (role: admin/kasir/gudang)

**Catatan**: Database, tabel, dan data dummy dibuat otomatis saat pertama akses!

---

### [65-75 Menit] PENGUJIAN SELF-HEALING

#### 1. Simulasikan Pod Failure
```bash
# Hapus pod app-toko secara manual
kubectl delete pod -n toko-lab -l app=app-toko
```

#### 2. Observasi Self-Healing
```bash
# Watch pod recreation
kubectl get pods -n toko-lab -w
```
**Expected**: K3s otomatis membuat pod baru untuk menjaga 2 replicas

#### 3. Hapus Pod MySQL
```bash
kubectl delete pod -n toko-lab -l app=mysql
```
**Expected**: Pod MySQL direcreate otomatis

#### 4. Verifikasi Aplikasi Masih Berjalan
Refresh browser, pastikan aplikasi masih bisa diakses.

#### 5. Cek Resource Usage
```bash
kubectl top pods -n toko-lab
```

---

### [75-80 Menit] WRAP-UP & EVALUASI

#### 1. Cleanup Deployment
```bash
kubectl delete namespace toko-lab
```

#### 2. Review Konsep yang Dipelajari
- **Pod**: Unit terkecil di Kubernetes
- **Deployment**: Manajemen pod dengan replicas & self-healing
- **Service**: Networking antar pod (ClusterIP vs NodePort)
- **ConfigMap/Secret**: Manajemen konfigurasi & kredensial
- **Microservices**: Arsitektur dengan service terpisah (Web + Proxy + DB)

#### 3. Tugas Evaluasi
- Screenshot halaman Dashboard aplikasi Toko
- Screenshot output `kubectl get pods -n toko-lab`
- Screenshot output `kubectl get svc -n toko-lab`
- Jelaskan arsitektur microservices yang telah dideploy

---

## 🔧 TROUBLESHOOTING

### Pod Status: ImagePullBackOff / ErrImagePull
**Cause**: Image belum di-import ke K3s
**Solution**:
```bash
docker save app-toko:latest -o app-toko.tar
sudo k3s ctr images import app-toko.tar
```

### Pod Status: CrashLoopBackOff
**Cause**: Aplikasi tidak bisa connect ke database
**Solution**:
```bash
kubectl logs -n toko-lab deployment/app-toko
kubectl logs -n toko-lab deployment/mysql
```
Pastikan ConfigMap dan Secret sudah benar.

### Tidak Bisa Akses Browser
**Cause**: Firewall memblokir NodePort
**Solution**:
```bash
sudo ufw allow 30080/tcp
```

### Database Connection Error
**Cause**: Env vars tidak terbaca
**Solution**:
Cek ConfigMap:
```bash
kubectl describe configmap app-config -n toko-lab
```

---

## 📚 KONSEP PENTING

### 1. Pod vs Deployment
- **Pod**: Satu instance container (ephemeral)
- **Deployment**: Manajer pod dengan replicas, self-healing, rolling update

### 2. Service Types
- **ClusterIP**: Akses internal antar pod di cluster
- **NodePort**: Akses eksternal via IP Node + Port (30000-32767)
- **LoadBalancer**: Akses eksternal dengan load balancer cloud (butuh cloud provider)

### 3. ConfigMap vs Secret
- **ConfigMap**: Konfigurasi non-sensitive (env vars, config files)
- **Secret**: Data sensitif (password, API keys, certificates)

### 4. Microservices Architecture
- **Web App**: PHP application logic
- **Nginx Proxy**: Reverse proxy untuk routing & load balancing
- **Database**: MySQL untuk persistensi data
- **Utility**: ConfigMap/Secret untuk environment configuration

---

## 🚀 DEPLOYMENT OTOMATIS (OPSIONAL)

Jika ingin menggunakan script otomatis:
```bash
cd ~/appToko-main
chmod +x deploy-local.sh
./deploy-local.sh
```

Script ini akan:
1. Build Docker images
2. Save & import ke K3s
3. Apply semua manifest
4. Tampilkan status deployment

---

## 📝 CATATAN PENTING

1. **Jangan gunakan Cloudflare Tunnel** di lab ini untuk menghindari setup DNS/Tunnel yang memakan waktu
2. **Semua service berjalan lokal** di dalam K3s cluster, tidak tergantung cloud publik
3. **Auto-DB Initialization**: Aplikasi appToko otomatis membuat database & tabel saat pertama akses
4. **Resource Limitation**: VM dengan RAM 2-4 GB sudah cukup untuk praktikum ini
5. **Self-healing**: K3s akan otomatis recreate pod yang crash/hilang

---

## ✅ CHECKLIST KELULUSAN

- [ ] K3s berhasil diinstall dan node status `Ready`
- [ ] Landing page cohort-01 berhasil diakses via NodePort
- [ ] Docker images app-toko dan nginx-toko berhasil dibuild
- [ ] Images berhasil di-import ke K3s containerd
- [ ] Semua pod (MySQL, App, Nginx) status `Running`
- [ ] Aplikasi CRUD berhasil diakses di browser
- [ ] Fitur CRUD (barang, kategori, POS, laporan) berfungsi
- [ ] Self-healing terbukti saat pod dihapus
- [ ] Cleanup namespace berhasil dilakukan

---

**Selamat Praktikum! 🎓**
