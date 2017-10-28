<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['admkonid'] = $this->session->userdata($this->sespre.'konid');
        $this->d['admnama'] = $this->session->userdata($this->sespre.'nama');
        $this->d['nama_form'] = "f_login";

        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['url'] = "home";

        cek_aktif();

        $wali_kelas = $this->session->userdata('app_rapot_walikelas');
        $this->d['id_kelas'] = $wali_kelas['id_walikelas'];
    }

    public function index() {
    	$q_jml_siswa = $this->db->query("SELECT 
                                        SUM(IF(a.jk='L',1,0)) jml_l,
                                        SUM(IF(a.jk='P',1,0)) jml_p
                                        FROM m_siswa a
                                        WHERE a.stat_data = 'A'")->row_array();
        $q_jml_guru = $this->db->query("SELECT COUNT(id) jml
                                        FROM m_guru a
                                        WHERE a.stat_data = 'A'")->row_array();

        $this->d['jml_siswa'] = $q_jml_siswa;
        $this->d['jml_guru'] = $q_jml_guru;

        $tasm = substr($this->d['tasm'],0,4);

        $q_jml_kelas = $this->db->query("SELECT 
                                        SUM(IF(b.jk='L',1,0)) jmlk_l,
                                        SUM(IF(b.jk='P',1,0)) jmlk_p
                                        FROM t_kelas_siswa a
                                        INNER JOIN m_siswa b ON a.id_siswa = b.id
                                        WHERE a.ta = '$tasm' AND a.id_kelas = '".$this->d['id_kelas']."'")->row_array();
        //echo $this->db->last_query();

        $this->d['stat_kelas'] = $q_jml_kelas;

        if ($this->d['admlevel'] != "siswa") {
            $this->d['p'] = "v_home";
        } else {
            $this->d['p'] = "v_home_siswa";
        }

        $this->load->view("template_utama", $this->d);
    }

    public function ubah_password() {

        $this->d['p'] = "v_ubah_password";
        $this->load->view("template_utama", $this->d);
    }

    public function simpan_ubah_password() {
        $id_user = $this->session->userdata('app_rapot_id');

        $cek_user = $this->db->query("SELECT id, username, password FROM m_admin WHERE id = $id_user")->row_array();

        $p = $this->input->post();

        $plama = sha1(sha1($p['p1']));

        $d = array();
        if (empty($cek_user)) {
            $d['status'] = "gagal";
            $d['data'] = "User tidak ditemukan";
        } else if ($p['username'] != $cek_user['username'])  {
            $d['status'] = "gagal";
            $d['data'] = "Username tidak ditemukan";
        } else if ($plama != $cek_user['password'])  {
            $d['status'] = "gagal";
            $d['data'] = "Password lama tidak cocok";
        } else if (strlen($p['p2']) < 6) {
            $d['status'] = "gagal";
            $d['data'] = "Password minimal 6 karakter";
        } else if ($p['p2'] != $p['p3']) {
            $d['status'] = "gagal";
            $d['data'] = "Password baru tidak sama";
        } else {
            $this->db->query("UPDATE m_admin SET password = '".sha1(sha1($p['p2']))."' WHERE id = '".$id_user."'");
            $d['status'] = "ok";
            $d['data'] = "Password berhasil diubah";
        }

        j($d);
        exit;
    }
}