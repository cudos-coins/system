<?php

namespace CC\Http\Controllers\API;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use CC\Http\Controllers\Controller;
use CC\Reservation;
use CC\Transaction;

/**
 * Handles the transaction requests.
 * @author b3nl <code@b3nl.de>
 * @category controllers
 * @package CC\Http
 * @subpackage API\Transactions
 * @version $id$
 */
class TransactionsController extends Controller
{
    public function index()
    {
        return __METHOD__;
    } // function

    public function store(Request $request, Authenticatable $user)
    {
        $data = [
            'amount' => $request->input('amount'),
            'description' => $request->input('description', ''),
            'from_user_id' => $request->input('from', $user->id),
            'planned_date' => date('Y-m-d H:i:s'),
            'to_user_id' => $request->input('to') // TODO Check
        ];

        // @TODO Check rights!
        if (($from = $data['from_user_id']) && ($from !== $user->id)) {
            return response('Forbidden', 403);
        } // if

        if (($user->balance - $data['amount']) < 0) { // TODO Check calc with huge numbers!
            return response('Forbidden', 403);
        } // if

        $reservation = Reservation::create($data);

        if (!$reservation->id) {
            return response('Internal Server Error', 500);
        } // if

        // TODO Create a loop for this.
        $updated = DB::table('users')
            ->whereIdAndBalanceAndLastTransactionId($user->id, $user->balance, $user->last_transaction_id)
            ->decrement('balance', $data['amount'],
                ['last_transaction_id' => $reservation->id]);  // TODO Check calc with huge numbers!

        if (!$updated) {
            return response('Conflict', 409);
        }

        $transaction = Transaction::find($reservation->id);
        $transaction->finished = 1;
        $transaction->save();

        // TODO Set Location header and status.
        return $this->show($transaction);
    } // function

    /**
     * Shows the requested transaction.
     * @param Transaction $transaction
     * @return Transaction
     * @todo Change the user model to a public model, there is no need to make the balance public.
     */
    public function show(Transaction $transaction)
    {
        /** @var \CC\User $user */
        $return = $transaction->toArray();

        $return['from_user'] = $transaction->fromUser;
        unset($return['from_user_id']);

        $return['to_user'] = $transaction->toUser;
        unset($return['to_user_id']);

        return $return;
    } // function
} // class
