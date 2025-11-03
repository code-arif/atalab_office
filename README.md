# 💖 Atalabe – Weekly Donation & Draw Platform (Laravel Backend)

Welcome to **Atalabe**, a secure and automated **weekly donation platform** built with **Laravel**.  
This system allows users to contribute voluntary donations, track real-time participation, and take part in a weekly draw that randomly selects a donor as the winner.

---

## 🚀 Key Features

### 🎯 Donation & Participation
- **Voluntary $25 donation system** – supports debit card payments only  
- **2-Step verification** for secure payment handling  
- **Real-time participant counter** updates dynamically with each donation  
- **Reverse countdown clock** for weekly draw tracking  

### 🗓️ Weekly Cycle Automation
- Donations **pause automatically every Sunday at 5 PM**  
- System **resumes Monday at 12 AM** for a new weekly cycle  
- **Automated random donor selection** every Sunday at 5 PM  
- Results stored in a **Winner Archive** with public recognition  

### 🏆 Winner Archive & Testimonials
- Display selected donors with name, photo, and week number  
- **Ceremonial design** for emotional and celebratory presentation  
- Admin control for managing archives and testimonials  

### 🏠 Dynamic Homepage
- Auto-rotating sliders for **stories, announcements, and visuals**  
- Real-time **live counters and countdown clocks**  
- Visually rich **3D-inspired responsive UI**

### ⚙️ Admin Panel
- Manage users, donations, and weekly draws  
- Control homepage content, testimonials, and archives  
- Built-in **CRM integration** for marketing updates and news  

### 💌 Email Notifications
- Automated confirmation emails for donors  
- Includes **timestamp**, **donor ID**, and **week number**  
- Secure mail handling with Laravel’s mailer configuration  

### 🔒 Security & Performance
- SSL-encrypted payment processing  
- Scalable architecture to handle thousands to millions of users  
- Optimized backend performance and reliability  

---

## 🛠️ Tech Stack

| Component | Technology |
|------------|-------------|
| **Backend** | Laravel (PHP Framework) |
| **Frontend** | React.js |
| **Database** | MySQL |
| **DevOps** | Node.js, NPM |
| **Asset Bundler** | Vite |
| **Design** | Figma (UI/UX Prototyping) |

---

## 📂 Project Structure

```bash
atalabe-backend/
├── app/              # Application logic (Models, Controllers, Services)
├── routes/           # API and web routes
├── resources/        # React components, JS, and Tailwind CSS
├── public/           # Public assets
├── database/         # Migrations, Seeders, Factories
└── ...

---

✅ **Why this works:**  
- The triple backticks (```) tell GitHub to treat everything inside as **preformatted code**, preserving indentation and symbols.  
- The optional `bash` after the first triple backtick adds a nice syntax highlight style.

Would you like me to apply this fix into your full README (so it’s copy-paste ready)?

