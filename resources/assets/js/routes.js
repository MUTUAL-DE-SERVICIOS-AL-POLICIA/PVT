import IndexLogin from '@/components/login/Index'
import Profile from '@/components/login/Profile'
import DashboardIndex from '@/components/dashboard/Index'
import UserIndex from '@/components/user/Index'
import RoleIndex from '@/components/role/Index'
import ProcedureTypeWorkflow from '@/components/procedure_type/Workflow'
import ProcedureTypeLoanDestiny from '@/components/procedure_type/LoanDestiny'
import AffiliateIndex from '@/components/affiliate/Index'
import AffiliateAdd from '@/components/affiliate/Add'
import LoanAdd from '@/components/loan/Add'
import RecordIndex from '@/components/record/Index'
import Camara from '@/components/affiliate/Webcam'
import FlowIndex from '@/components/workflow/Index'
import FlowAdd from '@/components/workflow/Add'
import PaymentAdd from '@/components/payment/Add'
import LoanPaymentIndex from '@/components/payment/Index'
import ImportExport from '@/components/payment/ImportExport'
import ListLoanGenerate from '@/components/payment/ListLoanGenerate'
import ListPaymentGenerate from '@/components/payment/ListPaymentGenerate'
import ChangeRol from '@/components/dashboard/ChangeRol'
import ListVouchers from '@/components/treasury/ListVouchers'
import Reports from '@/components/reports/Reports'

export const routes = [
  {
    name: 'cam',
    path: '/cam',
    component: Camara,
    meta: {
      requiresAuth: false
    }
  },
  {
    name: 'login',
    path: '/login',
    component: IndexLogin
  }, {
    name: 'profile',
    path: '/profile',
    component: Profile,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '*',
    redirect: {
      name: 'changeRol'
    },
    meta: {
      requiresAuth: true
    }
  }, 
  {
    path: '/changeRol',
    name: 'changeRol',
    component: ChangeRol,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/dashboard',
    name: 'dashboardIndex',
    component: DashboardIndex,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/user',
    name: 'userIndex',
    component: UserIndex,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/role',
    name: 'roleIndex',
    component: RoleIndex,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/procedure_type/workflow',
    name: 'procedureTypeWorkflow',
    component: ProcedureTypeWorkflow,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/procedure_type/loan_destiny',
    name: 'procedureTypeLoanDestiny',
    component: ProcedureTypeLoanDestiny,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/affiliate',
    name: 'affiliateIndex',
    component: AffiliateIndex,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/affiliate/:id',
    name: 'affiliateAdd',
    component: AffiliateAdd,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/record',
    name: 'recordIndex',
    component: RecordIndex,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/loan/:hash',
    name: 'loanAdd',
    component: LoanAdd,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/importExport',
    name: 'ImportExport',
    component: ImportExport,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/workflow',
    name: 'flowIndex',
    component: FlowIndex,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/workflow/:id',
    name: 'flowAdd',
    component: FlowAdd,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/listLoanGenerate',
    name: 'listLoanGenerate',
    component: ListLoanGenerate,
    meta: {
      requiresAuth: true
    }
  },
  //Cobros
  {
    path: '/kardex/:hash',
    name: 'paymentAdd',
    component: PaymentAdd,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/loanPayment',
    name: 'loanPaymentIndex',
    component: LoanPaymentIndex,
    meta: {
      requiresAuth: true
    }
  },
  
  {
    path: '/listPaymentGenerate',
    name: 'listPaymentGenerate',
    component: ListPaymentGenerate,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/listVouchers',
    name: 'listVouchers',
    component: ListVouchers,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/reports',
    name: 'reports',
    component: Reports,
    meta: {
      requiresAuth: true
    }
  },
]