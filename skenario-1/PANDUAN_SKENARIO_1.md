# PANDUAN SKENARIO 1 - LANDING PAGE
## Materi: K3s Single Container Deployment | XII TJKT 1 | 15 Menit

---

## 📋 TUJUAN
- Memahami konsep dasar **Pod** dan **Deployment** di Kubernetes (K3s)
- Deploy aplikasi **Landing Page** (Single Container)
- Memahami **Service NodePort** untuk akses eksternal
- Verifikasi K3s cluster berjalan dengan baik

---

## 🏗️ ARSITEKTUR
- **Single Container**: Nginx dengan HTML landing page
- **Service Type**: NodePort (akses via IP VM:30080)
- **Deployment**: 1 replica

---

## ⏱️ LANGKAH-LANGKAH

### 1. Setup K3s (jika belum terinstall)
```bash
curl -sfL https://get.k3s.io | sh -
```

### 2. Fix kubectl Permissions (PENTING)
Setelah install K3s, kubectl biasanya tidak bisa diakses oleh user biasa:
```bash
mkdir -p ~/.kube
sudo cp /etc/rancher/k3s/k3s.yaml ~/.kube/config
sudo chown $USER:$USER ~/.kube/config
export KUBECONFIG=~/.kube/config
echo 'export KUBECONFIG=~/.kube/config' >> ~/.bashrc
source ~/.bashrc
```

### 3. Verifikasi K3s Running
```bash
sudo systemctl status k3s
kubectl get nodes
```
**Expected Output**: Node status `Ready`

### 4. Cek K3s Version
```bash
kubectl version --client
```

### 5. Clone Repository
```bash
git clone https://github.com/Deri-Nugroho/k3s-deployment-training.git
cd k3s-deployment-training/skenario-1
```

---

## 🎯 OPSI DEPLOYMENT (PILIH SALAH SATU)

### OPSI 1: Manual Build dari Dockerfile (Untuk Pemula)
**Tujuan**: Memahami cara build Docker image manual

```bash
# Build image dari Dockerfile
docker build -t landing-page:latest .

# Save image ke tar
docker save landing-page:latest -o landing-page.tar

# Import ke K3s containerd
sudo k3s ctr images import landing-page.tar

# Deploy menggunakan manifest untuk image lokal
kubectl apply -f k3s-manifest-local.yaml
```

### OPSI 2: Deploy dari k3s-manifest.yaml (Menggunakan Image Pre-built)
**Tujuan**: Deploy cepat menggunakan image dari Docker Hub

```bash
# Deploy langsung menggunakan image dari Docker Hub
kubectl apply -f k3s-manifest.yaml
```

### OPSI 3: Deploy via CI/CD GitHub Actions (Otomatis)
**Tujuan**: Deploy otomatis saat push ke GitHub

**Prasyarat:**
- Setup K3s server dengan public IP
- Setup KUBECONFIG di GitHub Secrets

**Langkah:**
1. Push code ke GitHub
2. GitHub Actions akan otomatis:
   - Checkout repository
   - Apply manifest ke K3s cluster
3. Deployment otomatis berjalan

**File Workflow:** `.github/workflows/deploy-k3s.yaml`

---

## 🚀 LANJUTKAN DEPLOYMENT (Setelah pilih opsi di atas)

### 6. Cek Pod & Service
```bash
kubectl get pods
kubectl get svc
```
**Expected Output**:
- Pod: `nginx` status `Running`
- Service: `nginx` type `NodePort` port `30080`

### 7. Akses di Browser
Buka browser dan akses:
```
http://<IP-VM>:30080
```
**Expected**: Tampilan landing page HTML sederhana

### 8. Cek Pod Logs (opsional)
```bash
kubectl logs deployment/nginx
```

### 9. Cleanup Skenario 1
```bash
kubectl delete -f k3s-manifest.yaml
```

---

## 🔧 TROUBLESHOOTING

### Pod Status: ImagePullBackOff
**Cause**: Image tidak bisa di-pull dari Docker Hub
**Solution**:
```bash
kubectl describe pod nginx
```
Pastikan koneksi internet aktif untuk pull image `debuglifeindonesia/cohort-01:latest`

### Tidak Bisa Akses Browser
**Cause**: Firewall memblokir NodePort
**Solution**:
```bash
sudo ufw allow 30080/tcp
```

### Pod Status: Pending
**Cause**: K3s belum siap atau resource tidak cukup
**Solution**:
```bash
kubectl get nodes
kubectl describe pod nginx
```

---

## 📚 KONSEP YANG DIPELAJARI

### 1. Pod
- Unit terkecil di Kubernetes
- Berisi satu atau lebih container
- Ephemeral (bisa dihapus dan direcreate)

### 2. Deployment
- Manajer pod dengan replicas
- Self-healing (otomatis recreate pod yang crash)
- Rolling update untuk update aplikasi tanpa downtime

### 3. Service NodePort
- Expose pod ke eksternal cluster
- Menggunakan port range 30000-32767
- Akses via IP Node + Port (misal: 192.168.1.100:30080)

---

## ✅ CHECKLIST KELULUSAN

- [ ] K3s berhasil diinstall dan node status `Ready`
- [ ] Landing page berhasil di-deploy
- [ ] Pod nginx status `Running`
- [ ] Service nginx type `NodePort` dengan port `30080`
- [ ] Landing page berhasil diakses di browser
- [ ] Cleanup deployment berhasil dilakukan

---

**Lanjut ke Skenario 2: Multi-Container CRUD Microservices 🚀**
