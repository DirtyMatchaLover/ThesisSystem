# 🗑️ Data Clearing Guide

## Clear All Data (Keep User Accounts)

This feature allows you to **reset your system to a fresh state** while preserving all user accounts and credentials.

---

## 🎯 When to Use This:

### ✅ Good Reasons:
1. **New Academic Year** - Start fresh with no old theses
2. **Testing** - Clear test data before production
3. **Demo Reset** - Reset demo data for presentations
4. **Data Migration** - Clear before importing new data
5. **Fresh Start** - Remove all sample/test theses

### ❌ Don't Use If:
- You want to keep existing theses
- You're in production with real student data
- You haven't backed up important research

---

## 🚨 What Gets Deleted:

### ❌ Removed:
- **All thesis submissions** (database records)
- **All thesis comments and reviews**
- **All thesis categories and keywords**
- **All activity logs** (if created)
- **All user statistics** (login counts, etc.)
- **All analytics history**

### ✅ Preserved:
- **All user accounts** (students, faculty, admin, librarian)
- **Login credentials** (usernames and passwords)
- **User profiles** (names, emails, roles)
- **System configuration**
- **Categories and keywords** (master lists)

---

## 📁 Files on Disk:

### ⚠️ Manual Cleanup Required:
The script **DOES NOT** delete actual PDF files from:
```
uploads/theses/*.pdf
```

**Why?**
- For safety - prevents accidental deletion of important files
- Allows recovery if needed
- Files are orphaned but harmless

**To delete files manually:**
1. Go to `uploads/theses/` folder
2. Delete all PDF files
3. Keep the `.htaccess` file!

---

## 🔒 Security Features:

### 1. Confirmation Required
Must type exactly: `DELETE_ALL_DATA`

### 2. Admin-Only Access
Only visible to users with `role = 'admin'`

### 3. Double Confirmation
Browser popup asks "Are you ABSOLUTELY SURE?"

### 4. No Accidental Access
Cannot be triggered by URL alone

### 5. Foreign Key Safety
Properly handles database relationships

---

## 📋 Step-by-Step Usage:

### Step 1: Access the Tool
**Method A - Admin Menu:**
1. Login as Admin
2. Click your name (top right)
3. Click "🗑️ Clear All Data" (red text at bottom)

**Method B - Direct URL:**
```
http://localhost:8000/clear_all_data.php
```

### Step 2: Review Warning
Read the warning screen carefully:
- Lists what will be deleted
- Shows what will be preserved
- Explains manual cleanup needed

### Step 3: Type Confirmation
Type exactly: `DELETE_ALL_DATA`
(case-sensitive, no spaces)

### Step 4: Confirm
Click "Delete All Data (Keep Users)" button

### Step 5: Final Popup
Click "OK" on browser confirmation popup

### Step 6: Wait for Completion
Watch the progress as each table is cleared

### Step 7: Verify Success
Check summary showing:
- How many records deleted
- User count preserved
- Manual cleanup reminder

---

## 📊 What Happens to Research Analytics:

### After Clearing Data:

**Metrics will show:**
- Participants: (your real user counts)
- Submission Time: 10.0 minutes (default)
- Processing Speed: 3.2 seconds (default)
- Categorization Accuracy: 90% (default)
- Search Effectiveness: 85% (default)
- System Uptime: 98.5%
- Download Success: 92% (default)
- User Satisfaction: 4.00/5.0 (default)
- Recommendation Rate: 80% (default)

**Why defaults?**
- No thesis data exists yet
- Metrics need data to calculate real values
- As users upload theses, metrics update automatically

---

## 🔄 After Clearing:

### Immediate Effects:
✅ Users can still log in
✅ Users can upload new theses immediately
✅ System is fully functional
✅ All features work normally

### What to Do Next:
1. **Test Upload** - Have a user upload a test thesis
2. **Check Analytics** - Verify metrics update
3. **Delete PDF Files** - Manually clean `uploads/theses/` folder (optional)
4. **Clear Browser Cache** - Hard refresh (Ctrl+Shift+R)
5. **Start Fresh** - System is ready for new data!

---

## 🧪 Testing the Feature:

### Safe Test (Recommended):
1. **Backup database first:**
   ```bash
   mysqldump -u root thesis_db > backup.sql
   ```

2. **Use local development:**
   - Test on localhost, not production
   - Use test data only

3. **Verify user preservation:**
   - Check you can still log in
   - Verify all users still exist

4. **Test thesis upload:**
   - Upload a new thesis
   - Verify it appears in system
   - Check analytics update

---

## 💾 Backup First! (Recommended)

### Before Clearing Data:

**Option 1 - Full Database Backup:**
```bash
mysqldump -u root -p thesis_db > backup_$(date +%Y%m%d).sql
```

**Option 2 - Export via phpMyAdmin:**
1. Open phpMyAdmin
2. Select `thesis_db`
3. Click "Export"
4. Save SQL file

**Restore if needed:**
```bash
mysql -u root -p thesis_db < backup_20260202.sql
```

---

## 🚨 Troubleshooting:

### Error: "Foreign Key Constraint"
**Solution:** Script already handles this with `SET FOREIGN_KEY_CHECKS = 0`

### Error: "Table doesn't exist"
**Solution:** Normal - script skips non-existent tables

### Users Can't Log In After Clearing
**Solution:** This shouldn't happen! Users are preserved. Check:
- Database connection
- Session status
- User table integrity

### Analytics Shows Zero
**Solution:** Normal - no thesis data yet. Upload theses to populate.

### PDF Files Still Exist
**Solution:** Normal - must delete manually from `uploads/theses/`

---

## 📝 Technical Details:

### Tables Cleared (in order):
1. `thesis_revisions`
2. `thesis_comments`
3. `thesis_keywords`
4. `thesis_categories`
5. `user_activities` (if exists)
6. `user_sessions` (if exists)
7. `user_statistics` (if exists)
8. `theses` (last)

### Method Used:
```sql
TRUNCATE TABLE table_name;
```

**Why TRUNCATE?**
- Faster than DELETE
- Resets auto-increment IDs
- Releases storage immediately
- Safer than DROP (preserves structure)

---

## ⚡ Quick Commands:

### Access Data Clearing Tool:
```
http://localhost:8000/clear_all_data.php
```

### Backup Database:
```bash
mysqldump -u root thesis_db > backup.sql
```

### Restore Database:
```bash
mysql -u root thesis_db < backup.sql
```

### Delete PDF Files:
```bash
# Windows
del "C:\xampp\htdocs\thesis-management-system\uploads\theses\*.pdf"

# Linux/Mac
rm uploads/theses/*.pdf
```

---

## 🎯 Use Cases:

### Use Case 1: New Academic Year
**Scenario:** Start fresh semester with no old theses
**Steps:**
1. Backup database
2. Clear all data
3. Users continue with same accounts
4. Students upload new theses

### Use Case 2: Demo Reset
**Scenario:** Reset after demo presentation
**Steps:**
1. Clear all data
2. Keep user accounts for next demo
3. Upload sample theses again

### Use Case 3: Testing
**Scenario:** Test new features with clean slate
**Steps:**
1. Clear test data
2. Test upload functionality
3. Verify analytics work correctly
4. Test with multiple users

---

## ✅ Summary Checklist:

Before clearing:
- [ ] Backup database (if needed)
- [ ] Verify you're admin
- [ ] Confirm this is what you want

After clearing:
- [ ] Verify users can still log in
- [ ] Test thesis upload
- [ ] Check analytics show defaults
- [ ] Delete PDF files (optional)
- [ ] Clear browser cache

---

## 🎉 Benefits:

✅ **Safe** - Preserves user accounts
✅ **Fast** - Clears data in seconds
✅ **Clean** - Fresh start for new data
✅ **Reversible** - With database backup
✅ **Easy** - Simple confirmation process
✅ **Admin-Only** - Secured to administrators

**Perfect for starting a new academic year or testing your system!** 🚀
