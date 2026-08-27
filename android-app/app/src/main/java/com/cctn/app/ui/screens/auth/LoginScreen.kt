package com.cctn.app.ui.screens.auth

import android.content.Intent
import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.PersonAddAlt1
import androidx.compose.material3.Checkbox
import androidx.compose.material3.CheckboxDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.BiasAlignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.BuildConfig
import com.cctn.app.R
import com.cctn.app.ui.components.BrandWordmark
import com.cctn.app.ui.components.CctnPasswordField
import com.cctn.app.ui.components.CctnTextField
import com.cctn.app.ui.components.LoadingButton
import com.cctn.app.ui.components.OrDivider
import com.cctn.app.ui.components.SecondaryButton
import com.cctn.app.ui.theme.AuthOnScrim
import com.cctn.app.ui.theme.AuthScrimBottom
import com.cctn.app.ui.theme.AuthScrimTop
import com.cctn.app.ui.theme.LightSystemBarIcons
import com.google.android.gms.auth.api.signin.GoogleSignIn
import com.google.android.gms.auth.api.signin.GoogleSignInOptions
import com.google.android.gms.common.api.ApiException
import java.time.Year

/**
 * Sign in, laid out to match `auth/login.blade.php`: the office photo
 * behind a slate scrim, the lockup over it, and a white card holding
 * the form, remember-me checkbox, Google sign-in, and account registration.
 */
@Composable
fun LoginScreen(
    onRegister: () -> Unit,
    viewModel: LoginViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()

    LightSystemBarIcons()
    val context = LocalContext.current

    val googleSignInLauncher = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.StartActivityForResult()
    ) { result ->
        val task = GoogleSignIn.getSignedInAccountFromIntent(result.data)
        try {
            val account = task.getResult(ApiException::class.java)
            val idToken = account?.idToken
            if (!idToken.isNullOrBlank()) {
                viewModel.onGoogleIdToken(idToken)
            } else {
                viewModel.onGoogleSignInError("Google sign-in token could not be retrieved.")
            }
        } catch (e: ApiException) {
            if (e.statusCode != 12501 && e.statusCode != 12502) {
                viewModel.onGoogleSignInError("Google sign-in failed: ${e.localizedMessage ?: "Status ${e.statusCode}"}")
            }
        } catch (e: Exception) {
            viewModel.onGoogleSignInError("Google sign-in failed: ${e.localizedMessage ?: "Please try again."}")
        }
    }

    val handleGoogleSignIn = {
        val clientId = BuildConfig.GOOGLE_WEB_CLIENT_ID
        if (clientId.isNotBlank()) {
            val gso = GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
                .requestIdToken(clientId)
                .requestEmail()
                .build()
            val client = GoogleSignIn.getClient(context, gso)
            client.signOut().addOnCompleteListener {
                googleSignInLauncher.launch(client.signInIntent)
            }
        } else {
            context.startActivity(
                Intent(
                    Intent.ACTION_VIEW,
                    Uri.parse(BuildConfig.WEB_BASE_URL + "auth/google"),
                )
            )
        }
    }

    Box(Modifier.fillMaxSize()) {
        Image(
            painter = painterResource(R.drawable.login_bg),
            contentDescription = null,
            contentScale = ContentScale.Crop,
            alignment = BiasAlignment(horizontalBias = -0.55f, verticalBias = 0f),
            modifier = Modifier.fillMaxSize(),
        )
        Box(
            Modifier
                .fillMaxSize()
                .background(Brush.verticalGradient(listOf(AuthScrimTop, AuthScrimBottom)))
        )

        Column(
            modifier = Modifier
                .fillMaxSize()
                .statusBarsPadding()
                .verticalScroll(rememberScrollState())
                .imePadding()
                .navigationBarsPadding()
                .padding(horizontal = 20.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            Spacer(Modifier.height(44.dp))

            BrandWordmark(subtitle = "BANTAYAN", onDark = true, markSize = 44.dp)

            Spacer(Modifier.height(22.dp))

            AuthCard {
                Box(
                    modifier = Modifier
                        .size(64.dp)
                        .background(MaterialTheme.colorScheme.primary, CircleShape),
                    contentAlignment = Alignment.Center,
                ) {
                    Icon(
                        imageVector = Icons.Filled.Person,
                        contentDescription = null,
                        tint = Color.White,
                        modifier = Modifier.size(32.dp),
                    )
                }

                Spacer(Modifier.height(18.dp))

                Text(
                    text = "Client Login",
                    style = MaterialTheme.typography.headlineSmall,
                    color = MaterialTheme.colorScheme.onSurface,
                )
                Spacer(Modifier.height(6.dp))
                Text(
                    text = "Welcome back! Please sign in to continue.",
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    textAlign = TextAlign.Center,
                )

                Spacer(Modifier.height(26.dp))

                if (state.formError != null && state.loginInputError == null) {
                    FormErrorBanner(state.formError.orEmpty())
                    Spacer(Modifier.height(16.dp))
                }

                CctnTextField(
                    value = state.loginInput,
                    onValueChange = viewModel::onLoginInputChange,
                    label = "Email or Username",
                    placeholder = "Enter your email or username",
                    leadingIcon = Icons.Filled.Person,
                    error = state.loginInputError,
                    enabled = !state.submitting,
                    keyboardType = KeyboardType.Text,
                )

                CctnPasswordField(
                    value = state.password,
                    onValueChange = viewModel::onPasswordChange,
                    label = "Password",
                    placeholder = "Enter your password",
                    leadingIcon = Icons.Filled.Lock,
                    error = state.passwordError,
                    enabled = !state.submitting,
                    imeAction = ImeAction.Done,
                )

                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        modifier = Modifier.clickable { viewModel.onRememberMeChange(!state.rememberMe) },
                    ) {
                        Checkbox(
                            checked = state.rememberMe,
                            onCheckedChange = viewModel::onRememberMeChange,
                            colors = CheckboxDefaults.colors(
                                checkedColor = MaterialTheme.colorScheme.primary,
                            ),
                        )
                        Text(
                            text = "Remember me",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                    }

                    Text(
                        text = "Forgot Password?",
                        style = MaterialTheme.typography.labelMedium,
                        color = MaterialTheme.colorScheme.primary,
                        modifier = Modifier.clickable {
                            context.startActivity(
                                Intent(
                                    Intent.ACTION_VIEW,
                                    Uri.parse(BuildConfig.WEB_BASE_URL + "forgot-password"),
                                )
                            )
                        },
                    )
                }

                Spacer(Modifier.height(14.dp))

                LoadingButton(
                    text = "Sign In",
                    onClick = viewModel::submit,
                    modifier = Modifier.fillMaxWidth(),
                    loading = state.submitting,
                    enabled = state.canSubmit,
                    icon = Icons.AutoMirrored.Filled.ArrowForward,
                )

                Spacer(Modifier.height(18.dp))
                OrDivider()
                Spacer(Modifier.height(18.dp))

                SecondaryButton(
                    text = "Continue with Google",
                    onClick = handleGoogleSignIn,
                    modifier = Modifier.fillMaxWidth(),
                    enabled = !state.submitting,
                    painter = painterResource(R.drawable.ic_google),
                )

                Spacer(Modifier.height(12.dp))

                SecondaryButton(
                    text = "Create an Account",
                    onClick = onRegister,
                    modifier = Modifier.fillMaxWidth(),
                    enabled = !state.submitting,
                    icon = Icons.Filled.PersonAddAlt1,
                )

                Spacer(Modifier.height(20.dp))

                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.Center,
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Text(
                        text = "Are you staff or admin? ",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                    )
                    Text(
                        text = "Admin Login Here",
                        style = MaterialTheme.typography.labelMedium,
                        color = MaterialTheme.colorScheme.primary,
                        modifier = Modifier.clickable {
                            context.startActivity(
                                Intent(
                                    Intent.ACTION_VIEW,
                                    Uri.parse(BuildConfig.WEB_BASE_URL + "admin/login"),
                                )
                            )
                        },
                    )
                }
            }

            Spacer(Modifier.height(18.dp))

            Text(
                text = "© ${Year.now().value} BCTVI Bantayan. All rights reserved.",
                style = MaterialTheme.typography.bodySmall,
                color = AuthOnScrim,
                textAlign = TextAlign.Center,
            )

            Spacer(Modifier.height(36.dp))
        }
    }
}

/** `.auth-form-card` — white, a 20px radius and a heavy lift off the photo. */
@Composable
private fun AuthCard(content: @Composable ColumnScope.() -> Unit) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .widthIn(max = 440.dp)
            .background(MaterialTheme.colorScheme.surface, RoundedCornerShape(20.dp))
            .padding(horizontal = 24.dp, vertical = 30.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        content = content,
    )
}

/** The block the page prints validation failures in. */
@Composable
private fun FormErrorBanner(message: String) {
    val shape = RoundedCornerShape(8.dp)
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .background(Color(0xFFFEF2F2), shape)
            .border(1.dp, Color(0xFFFCA5A5), shape)
            .padding(horizontal = 12.dp, vertical = 10.dp),
    ) {
        Text(
            text = message,
            style = MaterialTheme.typography.bodySmall,
            color = Color(0xFF991B1B),
        )
    }
}
